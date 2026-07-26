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
                renderHTML: (attrs: Record<string, any>) => {
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
                renderHTML: (attrs: Record<string, any>) => {
                    if (!attrs.height) return {};
                    return { height: attrs.height };
                },
            },
            style: {
                default: null,
                parseHTML: (el: HTMLElement) => el.getAttribute('style') || null,
                renderHTML: (attrs: Record<string, any>) => {
                    if (!attrs.style) return {};
                    return { style: attrs.style };
                },
            },
            align: {
                default: null,
                parseHTML: (el: HTMLElement) => {
                    if (el.hasAttribute('data-align')) return el.getAttribute('data-align');
                    const style = el.getAttribute('style') || '';
                    if (style.includes('margin-left: auto') && style.includes('margin-right: auto')) return 'center';
                    if (style.includes('margin-left: 0') && style.includes('margin-right: auto')) return 'left';
                    if (style.includes('margin-left: auto') && style.includes('margin-right: 0')) return 'right';
                    return null;
                },
                renderHTML: (attrs: Record<string, any>) => {
                    if (!attrs.align) return {};
                    return { 'data-align': attrs.align };
                },
            },
            float: {
                default: null,
                parseHTML: (el: HTMLElement) => {
                    if (el.hasAttribute('data-float')) return el.getAttribute('data-float');
                    const style = el.getAttribute('style') || '';
                    const match = style.match(/float:\s*(left|right)/);
                    return match ? match[1] : null;
                },
                renderHTML: (attrs: Record<string, any>) => {
                    if (!attrs.float) return {};
                    return { 'data-float': attrs.float };
                },
            },
        };
    },

    addNodeView() {
        return ({ node, getPos }) => {
            const style = node.attrs.style || '';

            const floatValue = node.attrs.float || '';

            const marginLeftMatch = style.match(/margin-left:\s*(\d+)px/);
            const marginRightMatch = style.match(/margin-right:\s*(\d+)px/);
            const marginLeft = marginLeftMatch ? marginLeftMatch[1] : '';
            const marginRight = marginRightMatch ? marginRightMatch[1] : '';

            const wrapper = document.createElement('div');
            wrapper.className = 'resize-wrapper';
            wrapper.style.position = 'relative';
            wrapper.style.maxWidth = '100%';
            wrapper.style.lineHeight = '0';
            wrapper.style.fontSize = '0';

            if (node.attrs.align) {
                wrapper.setAttribute('data-align', node.attrs.align);
                wrapper.style.width = 'fit-content';
            }

            if (floatValue === 'left') {
                wrapper.setAttribute('data-float', 'left');
                wrapper.style.display = 'inline-block';
                wrapper.style.cssFloat = 'left';
                if (marginRight) wrapper.style.marginRight = `${marginRight}px`;
            } else if (floatValue === 'right') {
                wrapper.setAttribute('data-float', 'right');
                wrapper.style.display = 'inline-block';
                wrapper.style.cssFloat = 'right';
                if (marginLeft) wrapper.style.marginLeft = `${marginLeft}px`;
            } else if (!node.attrs.align) {
                wrapper.style.display = 'inline-block';
            }

            const img = document.createElement('img');
            img.src = node.attrs.src;
            img.alt = node.attrs.alt || '';
            img.title = node.attrs.title || '';
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
            img.style.display = 'block';
            img.style.pointerEvents = 'auto';

            if (node.attrs.align) img.setAttribute('data-align', node.attrs.align);
            if (node.attrs.float) img.setAttribute('data-float', node.attrs.float);

            const cleanStyle = style
                .replace(/float:\s*(left|right);?/g, '')
                .replace(/margin-left:\s*\d+px;?/g, '')
                .replace(/margin-right:\s*\d+px;?/g, '')
                .replace(/margin-left:\s*auto;?/g, '')
                .replace(/margin-right:\s*auto;?/g, '')
                .replace(/margin:\s*\d+px;?/g, '')
                .trim();

            if (cleanStyle) {
                img.style.cssText = cleanStyle;
            }

            const handles: HTMLElement[] = [];
            const handleCleanups: (() => void)[] = [];

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
                    (handle.style as any)[key] = value;
                });

                let isResizing = false;
                let resizeData: any = null;

                const onMouseMove = (e: MouseEvent) => {
                    if (!isResizing || !resizeData) return;

                    const { direction: dir, startX, startY, startWidth, startHeight, aspectRatio } = resizeData;
                    const dx = e.clientX - startX;
                    const dy = e.clientY - startY;
                    const isShift = e.shiftKey;

                    let newWidth = startWidth;
                    let newHeight = startHeight;

                    if (dir.includes('e')) {
                        newWidth = Math.max(MIN_SIZE, startWidth + dx);
                        if (isShift) newHeight = Math.max(MIN_SIZE, newWidth / aspectRatio);
                    }
                    if (dir.includes('w')) {
                        newWidth = Math.max(MIN_SIZE, startWidth - dx);
                        if (isShift) newHeight = Math.max(MIN_SIZE, newWidth / aspectRatio);
                    }
                    if (dir.includes('s')) {
                        newHeight = Math.max(MIN_SIZE, startHeight + dy);
                        if (isShift) newWidth = Math.max(MIN_SIZE, newHeight * aspectRatio);
                    }
                    if (dir.includes('n')) {
                        newHeight = Math.max(MIN_SIZE, startHeight - dy);
                        if (isShift) newWidth = Math.max(MIN_SIZE, newHeight * aspectRatio);
                    }

                    img.style.width = `${newWidth}px`;
                    img.style.height = `${newHeight}px`;

                    const pos = getPos();
                    if (typeof pos === 'number') {
                        document.dispatchEvent(new CustomEvent('image-resized', {
                            detail: { pos, width: newWidth, height: newHeight },
                        }));
                    }
                };

                const onMouseUp = () => {
                    if (isResizing) {
                        const pos = getPos();
                        if (typeof pos === 'number') {
                            document.dispatchEvent(new CustomEvent('image-resize-end', {
                                detail: { pos, width: img.style.width, height: img.style.height },
                            }));
                        }
                    }
                    isResizing = false;
                    resizeData = null;
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                };

                const handleMouseDown = (e: MouseEvent) => {
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
                };

                handle.addEventListener('mousedown', handleMouseDown);
                handleCleanups.push(() => {
                    handle.removeEventListener('mousedown', handleMouseDown);
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                });

                handles.push(handle);
                return handle;
            };

            HANDLES.forEach(({ direction, cursor, position }) => {
                wrapper.appendChild(createHandle(direction, cursor, position));
            });

            const onMouseEnter = () => handles.forEach(h => h.style.opacity = '1');
            const onMouseLeave = () => handles.forEach(h => h.style.opacity = '0');

            wrapper.addEventListener('mouseenter', onMouseEnter);
            wrapper.addEventListener('mouseleave', onMouseLeave);

            wrapper.appendChild(img);

            return {
                dom: wrapper,
                update: (updatedNode) => {
                    if (updatedNode.type !== node.type) return false;

                    img.src = updatedNode.attrs.src;
                    img.alt = updatedNode.attrs.alt || '';
                    img.title = updatedNode.attrs.title || '';

                    if (updatedNode.attrs.align) {
                        img.setAttribute('data-align', updatedNode.attrs.align);
                    } else {
                        img.removeAttribute('data-align');
                    }

                    if (updatedNode.attrs.float) {
                        img.setAttribute('data-float', updatedNode.attrs.float);
                    } else {
                        img.removeAttribute('data-float');
                    }

                    if (updatedNode.attrs.align) {
                        wrapper.setAttribute('data-align', updatedNode.attrs.align);
                        wrapper.style.width = 'fit-content';
                    } else {
                        wrapper.removeAttribute('data-align');
                        wrapper.style.width = '';
                    }

                    const updatedFloat = updatedNode.attrs.float || '';

                    wrapper.style.cssFloat = '';
                    wrapper.style.marginLeft = '';
                    wrapper.style.marginRight = '';
                    wrapper.removeAttribute('data-float');

                    if (updatedFloat === 'left') {
                        wrapper.setAttribute('data-float', 'left');
                        wrapper.style.display = 'inline-block';
                        wrapper.style.cssFloat = 'left';
                    } else if (updatedFloat === 'right') {
                        wrapper.setAttribute('data-float', 'right');
                        wrapper.style.display = 'inline-block';
                        wrapper.style.cssFloat = 'right';
                    } else if (!updatedNode.attrs.align) {
                        wrapper.style.display = 'inline-block';
                    }

                    const updatedStyle = updatedNode.attrs.style || '';
                    const cleanUpdatedStyle = updatedStyle
                        .replace(/float:\s*(left|right);?/g, '')
                        .replace(/margin-left:\s*\d+px;?/g, '')
                        .replace(/margin-right:\s*\d+px;?/g, '')
                        .replace(/margin-left:\s*auto;?/g, '')
                        .replace(/margin-right:\s*auto;?/g, '')
                        .replace(/margin:\s*\d+px;?/g, '')
                        .trim();

                    if (cleanUpdatedStyle) {
                        img.style.cssText = cleanUpdatedStyle;
                    }

                    node = updatedNode;
                    return true;
                },
                destroy: () => {
                    handleCleanups.forEach(cleanup => cleanup());
                    wrapper.removeEventListener('mouseenter', onMouseEnter);
                    wrapper.removeEventListener('mouseleave', onMouseLeave);
                },
            };
        };
    },
});
