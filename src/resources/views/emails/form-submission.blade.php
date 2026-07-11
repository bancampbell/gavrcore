<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Новое сообщение с формы</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #294469, #3688d1);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f8fafc;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .field {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .field-label {
            font-weight: 600;
            color: #475569;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .field-value {
            margin-top: 4px;
            color: #1e293b;
        }
        .meta {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 2px solid #e2e8f0;
            font-size: 13px;
            color: #64748b;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
<div class="header">
    <h1 style="margin: 0; font-size: 20px;">📩 Новое сообщение</h1>
    <p style="margin: 4px 0 0; opacity: 0.8;">Форма: {{ $form->title }}</p>
</div>

<div class="content">
    @foreach($data as $key => $value)
        <div class="field">
            <div class="field-label">{{ $key }}</div>
            <div class="field-value">{{ $value ?? '—' }}</div>
        </div>
    @endforeach

    <div class="meta">
        <p><strong>Отправитель:</strong> {{ $senderName ?? 'Аноним' }}</p>
        @if($senderEmail)
            <p><strong>Email:</strong> <a href="mailto:{{ $senderEmail }}">{{ $senderEmail }}</a></p>
        @endif
        @if($senderPhone)
            <p><strong>Телефон:</strong> <a href="tel:{{ $senderPhone }}">{{ $senderPhone }}</a></p>
        @endif
        <p><strong>Дата:</strong> {{ $submission->created_at->format('d.m.Y H:i') }}</p>
        <p><strong>IP адрес:</strong> {{ $submission->meta['ip'] ?? '—' }}</p>
    </div>
</div>

<div class="footer">
    GavrCore CMS · {{ now()->year }}
</div>
</body>
</html>
