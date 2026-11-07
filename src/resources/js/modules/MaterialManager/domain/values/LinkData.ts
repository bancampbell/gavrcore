export interface LinkPosition {
    from: number;
    to: number;
}

export class LinkData {
    public readonly url: string;
    public readonly text: string;
    public readonly target: '_self' | '_blank';
    public readonly title: string;
    public readonly oldText: string | null;
    // Позиционная идентификация ссылки в документе (getMarkRange на клике):
    // updateLink правит узел по этому диапазону, а не по текстовому поиску.
    public readonly _pos: LinkPosition | null;

    private constructor(
        url: string,
        text: string,
        target: '_self' | '_blank',
        title: string,
        oldText: string | null,
        _pos: LinkPosition | null,
    ) {
        this.url = url;
        this.text = text;
        this.target = target;
        this.title = title;
        this.oldText = oldText;
        this._pos = _pos;
    }

    static create(params: {
        url: string;
        text?: string;
        target?: string;
        title?: string;
        oldText?: string | null;
        _pos?: LinkPosition | null;
    }): LinkData {
        return new LinkData(
            params.url,
            params.text || params.url,
            params.target === '_blank' ? '_blank' : '_self',
            params.title || '',
            params.oldText || null,
            params._pos ?? null,
        );
    }

    withUrl(url: string): LinkData {
        return new LinkData(url, this.text, this.target, this.title, this.oldText, this._pos);
    }

    withText(text: string): LinkData {
        return new LinkData(this.url, text, this.target, this.title, this.oldText, this._pos);
    }

    withTarget(target: '_self' | '_blank'): LinkData {
        return new LinkData(this.url, this.text, target, this.title, this.oldText, this._pos);
    }

    withTitle(title: string): LinkData {
        return new LinkData(this.url, this.text, this.target, title, this.oldText, this._pos);
    }
}
