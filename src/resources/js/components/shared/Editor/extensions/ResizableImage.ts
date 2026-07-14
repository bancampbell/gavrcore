import { Image } from '@tiptap/extension-image';

type Direction = 'nw' | 'ne' | 'sw' | 'se' | 'n' | 's' | 'w' | 'e';

const HANDLE_SIZE = 14;
const MIN_SIZE = 50;
const HANDLE_OFFSET = -7;

const HANDLES: { direction: Direction; cursor: string; position: Record<string, string> }[] = [
    { direction: 'nw', cursor: 'nw-resize', position: { top: `${HANDLE_OFFSET}px`, left: `${HANDLE_OFFSET}px` } },
    { direction: 'ne', cursor: 'ne-resize', position: { top: `${HANDLE_OFFSET}px`, right: `${HANDLE_OFFSET}px` } },
    { direction: 'sw', cursor: 'sw-resize', position: { bottom: `${HANDLE_OFFSET}px`, left: `${HANDLE_OFFSET}px` } },
    { direction: 'se', cursor: 'se-resize', position: { bottom: `${HANDLE_OFFSET}px`, right: `${HANDLE_OFFSET}px` } },
    { direction: 'n', cursor: 'n-resize', position: { top: `${HANDLE_OFFSET}px`, left: '50%', transform: 'translateX(-50%)' } },
    { direction: 's', cursor: 's-resize', position: { bottom: `${HANDLE_OFFSET}px`, left: '50%', transform: 'translateX(-50%)' } },
    { direction: 'w', cursor: 'w-resize', position: { left: `${HANDLE_OFFSET}px`, top: '50%', transform: 'translateY(-50%)' } },
    { direction: 'e', cursor: 'e-resize', position: { right: `${HANDLE_OFFSET}px`, top: '50%', transform: 'translateY(-50%)' } },
];

const alignStyles: Record<string, string> = {
    left: 'margin-left: 0; margin-right: auto;',
    center: 'margin-left: auto; margin-right: auto;',
    right: 'margin-left: auto; margin-right: 0;',
};

function getAlign(style: string): string | null {
    if (style.includes('margin-left: auto') && style.includes('margin-right: auto')) return 'center';
    if (style.includes('margin-left: 0')) return 'left';
    if (style.includes('margin-right: 0')) return 'right';
    return null;
}

function getAlignStyle(align: string): string {
    return alignStyles[align] || '';
}

export const ResizableImage = Image.extend({
    addAttributes() {
        return {
            src: { default: null },
            alt: { default: null },
            title: { default: null },
            width: {
                default: null,
                parseHTML: (el: HTMLElement) => el.getAttribute('width'),
                renderHTML: (attrs: any) => attrs.width ? { width: attrs.width } : {},
            },
            height: {
                default: null,
                parseHTML: (el: HTMLElement) => el.getAttribute('height'),
                renderHTML: (attrs: any) => attrs.height ? { height: attrs.height } : {},
            },
            align: {
                default: null,
                parseHTML: (el: HTMLElement) => {
                    const style = el.getAttribute('style') || '';
                    return getAlign(style);
                },
                renderHTML: (attrs: any) => {
                    if (!attrs.align) return {};
                    return { style: `display: block; ${getAlignStyle(attrs.align)}` };
                },
            },
        };
    },

    addNodeView() {
        return ({ editor, node, getPos }) => {
            const wrapper = document.createElement('div');
            wrapper.style.position = 'relative';
            wrapper.style.maxWidth = '100%';
            wrapper.style.display = 'block';

            const container = document.createElement('div');
            container.style.position = 'relative';
            container.style.display = 'inline-block';
            container.style.maxWidth = '100%';
            container.style.border = '1px solid transparent';
            container.style.borderRadius = '4px';
            container.style.transition = 'border-color 0.2s';
            container.style.userSelect = 'none';

            const align = node.attrs.align || '';
            if (align === 'center') {
                wrapper.style.textAlign = 'center';
            } else if (align === 'left') {
                wrapper.style.textAlign = 'left';
            } else if (align === 'right') {
                wrapper.style.textAlign = 'right';
            }

            const img = document.createElement('img');
            img.src = node.attrs.src;
            img.alt = node.attrs.alt || '';
            img.title = node.attrs.title || '';
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
            img.style.display = 'block';
            img.style.pointerEvents = 'auto';

            if (node.attrs.width) img.style.width = `${node.attrs.width}px`;
            if (node.attrs.height) img.style.height = `${node.attrs.height}px`;
            if (node.attrs.align) {
                img.style.cssText += `display: block; ${getAlignStyle(node.attrs.align)}`;
            }

            const tooltip = document.createElement('div');
            tooltip.style.position = 'absolute';
            tooltip.style.bottom = '-30px';
            tooltip.style.left = '50%';
            tooltip.style.transform = 'translateX(-50%)';
            tooltip.style.background = 'rgba(0,0,0,0.85)';
            tooltip.style.color = '#fff';
            tooltip.style.padding = '3px 10px';
            tooltip.style.borderRadius = '4px';
            tooltip.style.fontSize = '12px';
            tooltip.style.fontFamily = 'monospace';
            tooltip.style.pointerEvents = 'none';
            tooltip.style.opacity = '0';
            tooltip.style.transition = 'opacity 0.25s';
            tooltip.style.whiteSpace = 'nowrap';
            tooltip.style.zIndex = '20';
            tooltip.textContent = `${img.offsetWidth} × ${img.offsetHeight}`;
            container.appendChild(tooltip);

            let isResizing = false;
            let resizeData: any = null;

            const updateTooltip = () => {
                tooltip.textContent = `${img.offsetWidth} × ${img.offsetHeight}`;
            };

            const updateImageAttrs = (width: number, height: number) => {
                const pos = getPos();
                if (typeof pos !== 'number') return;

                const tr = editor.view.state.tr;
                tr.setNodeMarkup(pos, undefined, {
                    ...node.attrs,
                    width: width,
                    height: height,
                    src: node.attrs.src,
                    alt: node.attrs.alt,
                    title: node.attrs.title,
                    align: node.attrs.align,
                    style: img.getAttribute('style') || '',
                });
                editor.view.dispatch(tr);
            };

            const createHandle = (direction: Direction, cursor: string, position: Record<string, string>) => {
                const handle = document.createElement('div');
                handle.className = 'resize-handle';
                handle.dataset.direction = direction;
                handle.style.position = 'absolute';
                handle.style.zIndex = '15';
                handle.style.opacity = '0';
                handle.style.transition = 'opacity 0.15s, transform 0.15s';
                handle.style.width = `${HANDLE_SIZE}px`;
                handle.style.height = `${HANDLE_SIZE}px`;
                handle.style.borderRadius = '50%';
                handle.style.background = '#577ebc';
                handle.style.border = '1px solid white';
                handle.style.boxShadow = '0 0 6px rgba(0,0,0,0.3)';
                handle.style.cursor = cursor;
                handle.style.pointerEvents = 'auto';

                Object.entries(position).forEach(([key, value]) => {
                    handle.style[key as any] = value;
                });

                handle.addEventListener('mouseenter', () => {
                    handle.style.transform = 'scale(1.3)';
                });
                handle.addEventListener('mouseleave', () => {
                    if (!isResizing) handle.style.transform = 'scale(1)';
                });

                handle.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    const rect = img.getBoundingClientRect();
                    const containerRect = container.getBoundingClientRect();

                    isResizing = true;
                    resizeData = {
                        direction,
                        startX: e.clientX,
                        startY: e.clientY,
                        startWidth: img.offsetWidth || 200,
                        startHeight: img.offsetHeight || 150,
                        startLeft: rect.left - containerRect.left,
                        startTop: rect.top - containerRect.top,
                        aspectRatio: (img.offsetWidth || 200) / (img.offsetHeight || 150),
                    };

                    tooltip.style.opacity = '1';
                    updateTooltip();
                    handle.style.transform = 'scale(1.3)';

                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                });

                return handle;
            };

            const onMouseMove = (e: MouseEvent) => {
                if (!isResizing || !resizeData) return;

                const { direction, startX, startY, startWidth, startHeight, startLeft, startTop, aspectRatio } = resizeData;
                const dx = e.clientX - startX;
                const dy = e.clientY - startY;
                const isShift = e.shiftKey;

                let newWidth = startWidth;
                let newHeight = startHeight;
                let newLeft = startLeft;
                let newTop = startTop;

                if (direction.includes('e')) {
                    newWidth = Math.max(MIN_SIZE, startWidth + dx);
                    if (isShift) newHeight = Math.max(MIN_SIZE, newWidth / aspectRatio);
                }
                if (direction.includes('w')) {
                    newWidth = Math.max(MIN_SIZE, startWidth - dx);
                    if (isShift) newHeight = Math.max(MIN_SIZE, newWidth / aspectRatio);
                    newLeft = startLeft + (startWidth - newWidth);
                }
                if (direction.includes('s')) {
                    newHeight = Math.max(MIN_SIZE, startHeight + dy);
                    if (isShift) newWidth = Math.max(MIN_SIZE, newHeight * aspectRatio);
                }
                if (direction.includes('n')) {
                    newHeight = Math.max(MIN_SIZE, startHeight - dy);
                    if (isShift) newWidth = Math.max(MIN_SIZE, newHeight * aspectRatio);
                    newTop = startTop + (startHeight - newHeight);
                }

                img.style.width = `${newWidth}px`;
                img.style.height = `${newHeight}px`;
                if (direction.includes('w') || direction.includes('n')) {
                    img.style.position = 'relative';
                    if (direction.includes('w')) img.style.left = `${newLeft}px`;
                    if (direction.includes('n')) img.style.top = `${newTop}px`;
                }

                updateTooltip();
                updateImageAttrs(newWidth, newHeight);
            };

            const onMouseUp = () => {
                isResizing = false;
                resizeData = null;
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);

                setTimeout(() => {
                    tooltip.style.opacity = '0';
                }, 600);

                container.querySelectorAll('.resize-handle').forEach((h) => {
                    (h as HTMLElement).style.transform = 'scale(1)';
                });
            };

            HANDLES.forEach(({ direction, cursor, position }) => {
                const handle = createHandle(direction, cursor, position);
                container.appendChild(handle);
            });

            container.addEventListener('mouseenter', () => {
                container.style.borderColor = '#5373a5';
                container.querySelectorAll('.resize-handle').forEach((h) => {
                    (h as HTMLElement).style.opacity = '1';
                });
            });

            container.addEventListener('mouseleave', () => {
                if (!isResizing) {
                    if (!img.classList.contains('selected-image')) {
                        container.style.borderColor = 'transparent';
                    }
                    container.querySelectorAll('.resize-handle').forEach((h) => {
                        (h as HTMLElement).style.opacity = '0';
                    });
                    tooltip.style.opacity = '0';
                }
            });

            img.addEventListener('click', (e) => {
                e.stopPropagation();
                document.querySelectorAll('.tiptap img').forEach(i => {
                    i.classList.remove('selected-image');
                    const parent = i.closest('div');
                    if (parent) parent.style.borderColor = 'transparent';
                });
                img.classList.add('selected-image');
                container.style.borderColor = '#748bb1';

                const style = img.getAttribute('style') || '';
                let imgAlign = '';
                if (style.includes('margin-left: auto') && style.includes('margin-right: auto')) {
                    imgAlign = 'center';
                } else if (style.includes('margin-left: 0')) {
                    imgAlign = 'left';
                } else if (style.includes('margin-right: 0')) {
                    imgAlign = 'right';
                }

                const imgData = {
                    url: img.src,
                    alt: img.alt || '',
                    title: img.title || '',
                    width: img.style.width || '',
                    height: img.style.height || '',
                    align: imgAlign,
                };
                const event = new CustomEvent('image-selected', { detail: imgData });
                document.dispatchEvent(event);
            });

            container.appendChild(img);
            wrapper.appendChild(container);

            img.addEventListener('load', updateTooltip);

            const observer = new ResizeObserver(updateTooltip);
            observer.observe(img);

            return {
                dom: wrapper,
                update: (updatedNode) => {
                    if (updatedNode.type !== node.type) return false;

                    img.src = updatedNode.attrs.src;
                    img.alt = updatedNode.attrs.alt || '';
                    img.title = updatedNode.attrs.title || '';

                    const align = updatedNode.attrs.align || '';
                    let width = updatedNode.attrs.width || '';
                    let height = updatedNode.attrs.height || '';

                    if (!width) {
                        const currentStyle = img.getAttribute('style') || '';
                        const wMatch = currentStyle.match(/width:\s*(\d+)px/);
                        if (wMatch) width = wMatch[1];
                    }
                    if (!height) {
                        const currentStyle = img.getAttribute('style') || '';
                        const hMatch = currentStyle.match(/height:\s*(\d+)px/);
                        if (hMatch) height = hMatch[1];
                    }

                    let style = 'display: block; ';
                    if (width) style += `width: ${width}px; `;
                    if (height) style += `height: ${height}px; `;
                    if (align) {
                        if (align === 'left') style += 'margin-left: 0; margin-right: auto;';
                        else if (align === 'center') style += 'margin-left: auto; margin-right: auto;';
                        else if (align === 'right') style += 'margin-left: auto; margin-right: 0;';
                    }

                    img.setAttribute('style', style);

                    if (width) img.setAttribute('width', width);
                    if (height) img.setAttribute('height', height);

                    if (align === 'center') {
                        wrapper.style.textAlign = 'center';
                    } else if (align === 'left') {
                        wrapper.style.textAlign = 'left';
                    } else if (align === 'right') {
                        wrapper.style.textAlign = 'right';
                    } else {
                        wrapper.style.textAlign = '';
                    }

                    // Обновляем ссылку на актуальный node
                    node = updatedNode;

                    updateTooltip();
                    return true;
                },
                destroy: () => {
                    observer.disconnect();
                    img.removeEventListener('load', updateTooltip);
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                },
            };
        };
    },
});
