import type { ImageStyleProps } from '../values/ImageData';

export class ImageStyleParser {
    static parse(style: string): ImageStyleProps {
        const props: ImageStyleProps = {};

        if (!style) return props;

        if (style.includes('margin-left: auto') && style.includes('margin-right: auto')) {
            props.align = 'center';
        } else if (style.includes('margin-left: 0') && !style.includes('margin-left: auto')) {
            props.align = 'left';
        } else if (style.includes('margin-right: 0') && !style.includes('margin-right: auto')) {
            props.align = 'right';
        }

        const floatMatch = style.match(/float:\s*(left|right)/);
        if (floatMatch) {
            props.float = floatMatch[1] as 'left' | 'right';
        }

        const marginRightMatch = style.match(/margin-right:\s*(\d+)px/);
        const marginLeftMatch = style.match(/margin-left:\s*(\d+)px/);
        if (props.float === 'left' && marginRightMatch) {
            props.margin = Number(marginRightMatch[1]);
        } else if (props.float === 'right' && marginLeftMatch) {
            props.margin = Number(marginLeftMatch[1]);
        }

        const widthMatch = style.match(/width:\s*(\d+)px/);
        if (widthMatch) props.width = Number(widthMatch[1]);

        const heightMatch = style.match(/height:\s*(\d+)px/);
        if (heightMatch) props.height = Number(heightMatch[1]);

        return props;
    }

    static build(props: ImageStyleProps): string {
        const parts: string[] = [];

        if (props.width) parts.push(`width: ${props.width}px`);
        if (props.height) parts.push(`height: ${props.height}px`);

        parts.push('display: block');

        switch (props.align) {
            case 'left':
                parts.push('margin-left: 0', 'margin-right: auto');
                break;
            case 'center':
                parts.push('margin-left: auto', 'margin-right: auto');
                break;
            case 'right':
                parts.push('margin-left: auto', 'margin-right: 0');
                break;
        }

        if (props.float === 'left') {
            parts.push('float: left');
            if (props.margin) parts.push(`margin-right: ${props.margin}px`);
        } else if (props.float === 'right') {
            parts.push('float: right');
            if (props.margin) parts.push(`margin-left: ${props.margin}px`);
        }

        return parts.join('; ').replace(/;\s*$/, '');
    }
}
