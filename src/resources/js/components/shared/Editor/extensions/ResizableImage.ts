import { Image } from '@tiptap/extension-image';

export const ResizableImage = Image.extend({
    addAttributes() {
        return {
            src: { default: null },
            alt: { default: null },
            title: { default: null },
            width: {
                default: null,
                parseHTML: (element: HTMLElement) => element.getAttribute('width'),
                renderHTML: (attributes: any) => {
                    if (!attributes.width) return {};
                    return { width: attributes.width };
                },
            },
            height: {
                default: null,
                parseHTML: (element: HTMLElement) => element.getAttribute('height'),
                renderHTML: (attributes: any) => {
                    if (!attributes.height) return {};
                    return { height: attributes.height };
                },
            },
        };
    },

    addNodeView() {
        return ({ editor, node, getPos }) => {
            const container = document.createElement('div');
            container.style.position = 'relative';
            container.style.display = 'inline-block';

            const img = document.createElement('img');
            img.src = node.attrs.src;
            img.alt = node.attrs.alt || '';
            img.title = node.attrs.title || '';
            img.style.maxWidth = '100%';
            img.style.height = 'auto';

            if (node.attrs.width) img.style.width = `${node.attrs.width}px`;
            if (node.attrs.height) img.style.height = `${node.attrs.height}px`;

            const resizeHandle = document.createElement('div');
            resizeHandle.style.position = 'absolute';
            resizeHandle.style.width = '12px';
            resizeHandle.style.height = '12px';
            resizeHandle.style.backgroundColor = '#3b82f6';
            resizeHandle.style.borderRadius = '50%';
            resizeHandle.style.border = '2px solid white';
            resizeHandle.style.cursor = 'nw-resize';
            resizeHandle.style.zIndex = '10';
            resizeHandle.style.opacity = '0';
            resizeHandle.style.transition = 'opacity 0.2s';

            const updateHandlePosition = () => {
                const imgRect = img.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();
                resizeHandle.style.right = `${containerRect.right - imgRect.right}px`;
                resizeHandle.style.bottom = `${containerRect.bottom - imgRect.bottom}px`;
            };

            container.addEventListener('mouseenter', () => {
                resizeHandle.style.opacity = '1';
                updateHandlePosition();
            });
            container.addEventListener('mouseleave', () => {
                resizeHandle.style.opacity = '0';
            });

            let isResizing = false;
            let startX = 0;
            let startWidth = 0;

            const onMouseMove = (e: MouseEvent) => {
                if (!isResizing) return;
                const dx = e.clientX - startX;
                const newWidth = Math.max(50, startWidth + dx);
                img.style.width = `${newWidth}px`;
                img.style.height = 'auto';

                if (getPos) {
                    const pos = getPos();
                    if (typeof pos === 'number') {
                        editor.commands.updateAttributes(node.type, { width: newWidth, height: null });
                    }
                }
                updateHandlePosition();
            };

            const onMouseUp = () => {
                isResizing = false;
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
            };

            resizeHandle.addEventListener('mousedown', (e) => {
                e.preventDefault();
                e.stopPropagation();
                isResizing = true;
                startX = e.clientX;
                startWidth = img.offsetWidth;
                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });

            container.appendChild(img);
            container.appendChild(resizeHandle);

            window.addEventListener('resize', updateHandlePosition);
            const observer = new MutationObserver(updateHandlePosition);
            observer.observe(container, { attributes: true, attributeFilter: ['style'] });

            return {
                dom: container,
                update: (updatedNode) => {
                    if (updatedNode.type !== node.type) return false;
                    img.src = updatedNode.attrs.src;
                    img.alt = updatedNode.attrs.alt || '';
                    img.title = updatedNode.attrs.title || '';
                    if (updatedNode.attrs.width) img.style.width = `${updatedNode.attrs.width}px`;
                    img.style.height = 'auto';
                    updateHandlePosition();
                    return true;
                },
                destroy: () => {
                    window.removeEventListener('resize', updateHandlePosition);
                    observer.disconnect();
                },
            };
        };
    },
});
