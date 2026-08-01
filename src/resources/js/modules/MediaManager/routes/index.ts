// resources/js/modules/MediaManager/routes/index.ts

export const mediaRoutes = {
    index: 'admin.media.index',
    contents: 'admin.media.contents',
    contentsPaginated: 'admin.media.contents.paginated',
    folders: 'admin.media.folders',
    folderCreate: 'admin.media.folder.create',
    rename: 'admin.media.rename',
    copy: 'admin.media.copy',
    upload: 'admin.media.upload',
    itemDelete: 'admin.media.item.delete',
    itemsDelete: 'admin.media.items.delete',
} as const;
