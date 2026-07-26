export class LinkData {
    public readonly url: string;
    public readonly text: string;
    public readonly target: '_self' | '_blank';
    public readonly title: string;
    public readonly oldText: string | null;

    private constructor(
        url: string,
        text: string,
        target: '_self' | '_blank',
        title: string,
        oldText: string | null,
    ) {
        this.url = url;
        this.text = text;
        this.target = target;
        this.title = title;
        this.oldText = oldText;
    }

    static create(params: {
        url: string;
        text?: string;
        target?: string;
        title?: string;
        oldText?: string | null;
    }): LinkData {
        return new LinkData(
            params.url,
            params.text || params.url,
            params.target === '_blank' ? '_blank' : '_self',
            params.title || '',
            params.oldText || null,
        );
    }

    withUrl(url: string): LinkData {
        return new LinkData(url, this.text, this.target, this.title, this.oldText);
    }

    withText(text: string): LinkData {
        return new LinkData(this.url, text, this.target, this.title, this.oldText);
    }

    withTarget(target: '_self' | '_blank'): LinkData {
        return new LinkData(this.url, this.text, target, this.title, this.oldText);
    }

    withTitle(title: string): LinkData {
        return new LinkData(this.url, this.text, this.target, title, this.oldText);
    }
}
