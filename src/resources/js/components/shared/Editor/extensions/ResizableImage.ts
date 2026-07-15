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

export const ResizableImage = Image.extend({
    addAttributes() {
        return {
            src: { default: null },
            alt: { default: null },
            title: { default: null },
            width: {
                default: null,
                parseHTML: (el: HTMLElement) => {
                    const style = el.getAttribute('style') || '';
                    const match = style.match(/width:\s*(\d+)px/);
                    return match ? match[1] : el.getAttribute('width') || null;
                },
                renderHTML: (attrs: any) => {
                    if (!attrs.width) return {};
                    return { width: attrs.width };
                },
            },
            height: {
                default: null,
                parseHTML: (el: HTMLElement) => {
                    const style = el.getAttribute('style') || '';
                    const match = style.match(/height:\s*(\d+)px/);
                    return match ? match[1] : el.getAttribute('height') || null;
                },
                renderHTML: (attrs: any) => {
                    if (!attrs.height) return {};
                    return { height: attrs.height };
                },
            },
            style: {
                default: null,
                parseHTML: (el: HTMLElement) => el.getAttribute('style') || null,
                renderHTML: (attrs: any) => {
                    if (!attrs.style) return {};
                    return { style: attrs.style };
                },
            },
        };
    },

    addNodeView() {
        return ({ editor, node, getPos }) => {
            const style = node.attrs.style || '';

            const floatMatch = style.match(/float:\s*(left|right)/);
            const floatValue = floatMatch ? floatMatch[1] : '';

            const marginLeftMatch = style.match(/margin-left:\s*(\d+)px/);
            const marginRightMatch = style.match(/margin-right:\s*(\d+)px/);
            const marginLeft = marginLeftMatch ? marginLeftMatch[1] : '';
            const marginRight = marginRightMatch ? marginRightMatch[1] : '';

            const wrapper = document.createElement('div');
            wrapper.className = 'resize-wrapper';
            wrapper.style.position = 'relative';
            wrapper.style.display = 'inline-block';
            wrapper.style.maxWidth = '100%';
            wrapper.style.lineHeight = '0';
            wrapper.style.fontSize = '0';

            if (floatValue === 'left') {
                wrapper.style.cssFloat = 'left';
                if (marginRight) wrapper.style.marginRight = `${marginRight}px`;
            } else if (floatValue === 'right') {
                wrapper.style.cssFloat = 'right';
                if (marginLeft) wrapper.style.marginLeft = `${marginLeft}px`;
            }

            const img = document.createElement('img');
            img.src = node.attrs.src;
            img.alt = node.attrs.alt || '';
            img.title = node.attrs.title || '';
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
            img.style.display = 'block';
            img.style.pointerEvents = 'auto';

            let cleanStyle = style
                .replace(/float:\s*(left|right);?/, '')
                .replace(/margin(?:-left|-right)?:\s*\d+px;?/, '')
                .trim();
            if (cleanStyle) {
                img.style.cssText = cleanStyle;
            }

            let currentFloat = floatValue;
            let currentMarginLeft = marginLeft;
            let currentMarginRight = marginRight;

            const handles: HTMLElement[] = [];

            const createHandle = (direction: Direction, cursor: string, position: Record<string, string>) => {
                const handle = document.createElement('div');
                handle.className = 'resize-handle';
                handle.dataset.direction = direction;
                handle.style.position = 'absolute';
                handle.style.zIndex = '999';
                handle.style.opacity = '0';
                handle.style.transition = 'opacity 0.15s';
                handle.style.width = `${HANDLE_SIZE}px`;
                handle.style.height = `${HANDLE_SIZE}px`;
                handle.style.borderRadius = '50%';
                handle.style.background = '#577ebc';
                handle.style.border = '2px solid white';
                handle.style.boxShadow = '0 0 8px rgba(0,0,0,0.4)';
                handle.style.cursor = cursor;
                handle.style.pointerEvents = 'auto';

                Object.entries(position).forEach(([key, value]) => {
                    handle.style[key as any] = value;
                });

                let isResizing = false;
                let resizeData: any = null;

                const onMouseMove = (e: MouseEvent) => {
                    if (!isResizing || !resizeData) return;

                    const { direction, startX, startY, startWidth, startHeight, aspectRatio } = resizeData;
                    const dx = e.clientX - startX;
                    const dy = e.clientY - startY;
                    const isShift = e.shiftKey;

                    let newWidth = startWidth;
                    let newHeight = startHeight;

                    if (direction.includes('e')) {
                        newWidth = Math.max(MIN_SIZE, startWidth + dx);
                        if (isShift) newHeight = Math.max(MIN_SIZE, newWidth / aspectRatio);
                    }
                    if (direction.includes('w')) {
                        newWidth = Math.max(MIN_SIZE, startWidth - dx);
                        if (isShift) newHeight = Math.max(MIN_SIZE, newWidth / aspectRatio);
                    }
                    if (direction.includes('s')) {
                        newHeight = Math.max(MIN_SIZE, startHeight + dy);
                        if (isShift) newWidth = Math.max(MIN_SIZE, newHeight * aspectRatio);
                    }
                    if (direction.includes('n')) {
                        newHeight = Math.max(MIN_SIZE, startHeight - dy);
                        if (isShift) newWidth = Math.max(MIN_SIZE, newHeight * aspectRatio);
                    }

                    img.style.width = `${newWidth}px`;
                    img.style.height = `${newHeight}px`;

                    const pos = getPos();
                    if (typeof pos === 'number') {
                        const { state, view } = editor;
                        const tr = state.tr;

                        let newStyle = `width: ${newWidth}px; height: ${newHeight}px;`;
                        if (currentFloat) {
                            newStyle += ` float: ${currentFloat};`;
                        }
                        if (currentFloat === 'left' && currentMarginRight) {
                            newStyle += ` margin-right: ${currentMarginRight}px;`;
                        }
                        if (currentFloat === 'right' && currentMarginLeft) {
                            newStyle += ` margin-left: ${currentMarginLeft}px;`;
                        }

                        const attrs = {
                            ...node.attrs,
                            width: newWidth,
                            height: newHeight,
                            style: newStyle.trim(),
                        };
                        tr.setNodeMarkup(pos, undefined, attrs);
                        view.dispatch(tr);
                    }
                };

                const onMouseUp = () => {
                    isResizing = false;
                    resizeData = null;
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                };

                handle.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    isResizing = true;
                    resizeData = {
                        direction,
                        startX: e.clientX,
                        startY: e.clientY,
                        startWidth: img.offsetWidth || 200,
                        startHeight: img.offsetHeight || 150,
                        aspectRatio: (img.offsetWidth || 200) / (img.offsetHeight || 150),
                    };

                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                });

                handles.push(handle);
                return handle;
            };

            HANDLES.forEach(({ direction, cursor, position }) => {
                const handle = createHandle(direction, cursor, position);
                wrapper.appendChild(handle);
            });

            wrapper.addEventListener('mouseenter', () => {
                handles.forEach(h => h.style.opacity = '1');
            });

            wrapper.addEventListener('mouseleave', () => {
                handles.forEach(h => h.style.opacity = '0');
            });

            img.addEventListener('click', (e) => {
                e.stopPropagation();
                document.querySelectorAll('.tiptap img').forEach(i => {
                    i.classList.remove('selected-image');
                });
                img.classList.add('selected-image');

                // Передаем в событие данные о float и margin из wrapper
                const wrapperStyle = wrapper.style.cssFloat || '';
                let floatVal = '';
                let marginVal = '';
                if (wrapperStyle === 'left') {
                    floatVal = 'left';
                    marginVal = wrapper.style.marginRight ? wrapper.style.marginRight.replace('px', '') : '';
                } else if (wrapperStyle === 'right') {
                    floatVal = 'right';
                    marginVal = wrapper.style.marginLeft ? wrapper.style.marginLeft.replace('px', '') : '';
                }

                const imgStyle = img.getAttribute('style') || '';
                const imgData = {
                    url: img.src,
                    alt: img.alt || '',
                    title: img.title || '',
                    width: img.style.width || '',
                    height: img.style.height || '',
                    align: imgStyle.includes('margin-left: auto') && imgStyle.includes('margin-right: auto') ? 'center' :
                        imgStyle.includes('margin-left: 0') ? 'left' :
                            imgStyle.includes('margin-right: 0') ? 'right' : '',
                    float: floatVal,
                    margin: marginVal,
                };
                const event = new CustomEvent('image-selected', { detail: imgData });
                document.dispatchEvent(event);
            });

            wrapper.appendChild(img);

            return {
                dom: wrapper,
                update: (updatedNode) => {
                    if (updatedNode.type !== node.type) return false;

                    img.src = updatedNode.attrs.src;
                    img.alt = updatedNode.attrs.alt || '';
                    img.title = updatedNode.attrs.title || '';

                    if (updatedNode.attrs.style) {
                        const style = updatedNode.attrs.style;

                        const floatMatch = style.match(/float:\s*(left|right)/);
                        const marginLeftMatch = style.match(/margin-left:\s*(\d+)px/);
                        const marginRightMatch = style.match(/margin-right:\s*(\d+)px/);

                        const floatVal = floatMatch ? floatMatch[1] : '';
                        const marginLeft = marginLeftMatch ? marginLeftMatch[1] : '';
                        const marginRight = marginRightMatch ? marginRightMatch[1] : '';

                        wrapper.style.cssFloat = floatVal;
                        if (floatVal === 'left') {
                            wrapper.style.marginRight = marginRight ? `${marginRight}px` : '';
                            wrapper.style.marginLeft = '';
                        } else if (floatVal === 'right') {
                            wrapper.style.marginLeft = marginLeft ? `${marginLeft}px` : '';
                            wrapper.style.marginRight = '';
                        } else {
                            wrapper.style.marginLeft = '';
                            wrapper.style.marginRight = '';
                        }

                        let cleanStyle = style
                            .replace(/float:\s*(left|right);?/, '')
                            .replace(/margin(?:-left|-right)?:\s*\d+px;?/, '')
                            .trim();
                        if (cleanStyle) {
                            img.style.cssText = cleanStyle;
                        }
                    }

                    node = updatedNode;
                    return true;
                },
                destroy: () => {},
            };
        };
    },
});
