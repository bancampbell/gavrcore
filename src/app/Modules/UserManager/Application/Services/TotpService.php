<?php

namespace App\Modules\UserManager\Application\Services;

/**
 * TOTP по RFC 6238 (HMAC-SHA1, 30-секундное окно, 6 цифр) + резервные коды.
 * Самодостаточная реализация без внешних зависимостей (composer.json не
 * содержит TOTP-библиотек): base32-секрет 160 бит, допуск ±1 окно при проверке.
 *
 * Сервис stateless — всё состояние (секрет, хеши кодов) хранится на модели
 * пользователя, поэтому подмены реализации не требуется и интерфейс не заводим
 * (по аналогии с SettingService/ThemeManager, а не со SlugGeneratorInterface).
 */
final class TotpService
{
    private const SECRET_BYTES = 20; // 160 бит → 32 символа base32

    private const PERIOD = 30;

    private const DIGITS = 6;

    private const WINDOW = 1; // допуск ±1 окно (текущее + соседние)

    private const RECOVERY_CODES_COUNT = 8;

    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Генерация нового base32-секрета (без padding-символов '=').
     */
    public function generateSecret(): string
    {
        return self::base32Encode(random_bytes(self::SECRET_BYTES));
    }

    /**
     * Код для секрета в заданный момент времени (по умолчанию — сейчас).
     */
    public function generateCode(string $secret, ?int $timestamp = null): string
    {
        $counter = intdiv($timestamp ?? time(), self::PERIOD);

        $time = pack('J', $counter); // 8 байт, big-endian
        $hash = hash_hmac('sha1', $time, self::base32Decode($secret), true);

        // Динамическая усечённая подпись (RFC 4226, §5.3).
        $offset = ord($hash[19]) & 0x0F;
        $binary = (
            ((ord($hash[$offset]) & 0x7F) << 24)
            | (ord($hash[$offset + 1]) << 16)
            | (ord($hash[$offset + 2]) << 8)
            | ord($hash[$offset + 3])
        );

        return str_pad((string) ($binary % 10 ** self::DIGITS), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Проверка кода с допуском ±1 окно относительно $timestamp.
     */
    public function verify(string $secret, string $code, ?int $timestamp = null): bool
    {
        $timestamp ??= time();

        for ($shift = -self::WINDOW; $shift <= self::WINDOW; $shift++) {
            if (hash_equals($this->generateCode($secret, $timestamp + $shift * self::PERIOD), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * otpauth:// URL для добавления в приложение-аутентификатор.
     * Формат: otpauth://totp/{issuer}:{account}?secret=...&issuer=...
     *         &algorithm=SHA1&digits=6&period=30
     */
    public function buildOtpAuthUrl(string $secret, string $accountName, string $issuer): string
    {
        $label = rawurlencode($issuer).':'.rawurlencode($accountName);
        $query = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => self::DIGITS,
            'period' => self::PERIOD,
        ]);

        return "otpauth://totp/{$label}?{$query}";
    }

    /**
     * 8 резервных кодов в открытом виде (вида "A1B2-C3D4").
     * Возвращаются пользователю ровно один раз — при confirm; хранятся
     * на модели только хеши (см. hashRecoveryCode()).
     *
     * @return array<int, string>
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];

        for ($i = 0; $i < self::RECOVERY_CODES_COUNT; $i++) {
            $hex = strtoupper(bin2hex(random_bytes(4))); // 8 hex-символов
            $codes[] = substr($hex, 0, 4).'-'.substr($hex, 4);
        }

        return $codes;
    }

    /**
     * Хеш резервного кода для хранения. Нормализация: верхний регистр,
     * без '-' и пробелов — код принимается в любом написании.
     */
    public function hashRecoveryCode(string $code): string
    {
        return hash('sha256', $this->normalizeRecoveryCode($code));
    }

    /**
     * @param  array<int, string>  $hashedCodes
     */
    public function recoveryCodeMatches(string $code, array $hashedCodes): bool
    {
        return in_array($this->hashRecoveryCode($code), $hashedCodes, true);
    }

    /**
     * "Сжигание" использованного резервного кода: возвращает массив хешей
     * без сгоревшего кода либо null, если код не найден. Персистентность —
     * на вызывающей стороне (логин, часть 1б).
     *
     * @param  array<int, string>  $hashedCodes
     * @return array<int, string>|null
     */
    public function burnRecoveryCode(string $code, array $hashedCodes): ?array
    {
        $hash = $this->hashRecoveryCode($code);
        $index = array_search($hash, $hashedCodes, true);

        if ($index === false) {
            return null;
        }

        unset($hashedCodes[$index]);

        return array_values($hashedCodes);
    }

    private function normalizeRecoveryCode(string $code): string
    {
        return strtoupper(str_replace(['-', ' '], '', trim($code)));
    }

    private static function base32Encode(string $data): string
    {
        $encoded = '';
        $buffer = 0;
        $bitsLeft = 0;

        foreach (str_split($data) as $byte) {
            $buffer = ($buffer << 8) | ord($byte);
            $bitsLeft += 8;

            while ($bitsLeft >= 5) {
                $bitsLeft -= 5;
                $encoded .= self::BASE32_ALPHABET[($buffer >> $bitsLeft) & 0x1F];
            }
        }

        if ($bitsLeft > 0) {
            $encoded .= self::BASE32_ALPHABET[($buffer << (5 - $bitsLeft)) & 0x1F];
        }

        return $encoded;
    }

    private static function base32Decode(string $encoded): string
    {
        $decoded = '';
        $buffer = 0;
        $bitsLeft = 0;
        $encoded = strtoupper(rtrim($encoded, '='));

        foreach (str_split($encoded) as $char) {
            $value = strpos(self::BASE32_ALPHABET, $char);

            if ($value === false) {
                throw new \InvalidArgumentException('Некорректный base32-символ в TOTP-секрете');
            }

            $buffer = ($buffer << 5) | $value;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $decoded .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $decoded;
    }
}
