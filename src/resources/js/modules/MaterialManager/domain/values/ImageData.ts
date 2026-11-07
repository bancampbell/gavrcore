export interface ImageStyleProps {
    width?: number;
    height?: number;
    align?: 'left' | 'center' | 'right';
    float?: 'left' | 'right';
    margin?: number;
}

export class ImageData {
    public readonly url: string;
    public readonly alt: string;
    public readonly title: string;
    public readonly width: number | null;
    public readonly height: number | null;
    public readonly styleProps: ImageStyleProps;

    private constructor(
        url: string,
        alt: string,
        title: string,
        width: number | null,
        height: number | null,
        styleProps: ImageStyleProps,
    ) {
        this.url = url;
        this.alt = alt;
        this.title = title;
        this.width = width;
        this.height = height;
        this.styleProps = styleProps;
    }

    static create(params: {
        url: string;
        alt?: string;
        title?: string;
        width?: string | number | null;
        height?: string | number | null;
        align?: string;
        float?: string;
        margin?: string | number | null;
    }): ImageData {
        return new ImageData(
            params.url,
            params.alt || '',
            params.title || '',
            params.width ? Number(params.width) : null,
            params.height ? Number(params.height) : null,
            {
                align: (params.align as ImageStyleProps['align']) || undefined,
                float: (params.float as ImageStyleProps['float']) || undefined,
                margin: params.margin ? Number(params.margin) : undefined,
            },
        );
    }

    withUrl(url: string): ImageData {
        return new ImageData(url, this.alt, this.title, this.width, this.height, this.styleProps);
    }

    withAlt(alt: string): ImageData {
        return new ImageData(this.url, alt, this.title, this.width, this.height, this.styleProps);
    }

    withTitle(title: string): ImageData {
        return new ImageData(this.url, this.alt, title, this.width, this.height, this.styleProps);
    }

    withDimensions(width: number | null, height: number | null): ImageData {
        return new ImageData(this.url, this.alt, this.title, width, height, this.styleProps);
    }

    withStyleProps(styleProps: ImageStyleProps): ImageData {
        return new ImageData(this.url, this.alt, this.title, this.width, this.height, styleProps);
    }
}
