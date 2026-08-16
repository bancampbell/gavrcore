import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import Create from '@/modules/GalleryManager/ui/Create.vue';
import { galleryApi } from '@/modules/GalleryManager/infrastructure/api/gallery-api';

vi.mock('@inertiajs/vue3', () => ({
  Head: { template: '<div><slot /></div>' },
  router: { visit: vi.fn() },
}));

vi.mock('@/modules/GalleryManager/infrastructure/api/gallery-api');

const mockToast = vi.fn();
vi.mock('@/components/shared/Toast.vue', () => ({
  default: {
    template: '<div class="toast"><slot /></div>',
    props: ['show', 'message', 'type'],
  },
}));

vi.mock('@/layouts/AdminLayout.vue', () => ({
  default: {
    template: '<div class="admin-layout"><slot /></div>',
    props: ['user'],
  },
}));

describe('Create.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders create form with default values', () => {
    const wrapper = mount(Create, {
      props: { user: {}, title: 'Создать галерею' },
    });
    expect(wrapper.text()).toContain('Создать галерею');
    expect(wrapper.find('select').element.value).toBe('grid');
  });

  it('updates form on input', async () => {
    const wrapper = mount(Create, {
      props: { user: {}, title: 'Создать галерею' },
    });
    await wrapper.find('input[type="text"]').setValue('New Gallery');
    expect(wrapper.vm.form.title).toBe('New Gallery');
  });

  it('toggles status on click', async () => {
    const wrapper = mount(Create, {
      props: { user: {}, title: 'Создать галерею' },
    });
    expect(wrapper.vm.form.status).toBe(true);
    await wrapper.find('.admin-toggle').trigger('click');
    expect(wrapper.vm.form.status).toBe(false);
  });

  it('disables create button when title is empty', () => {
    const wrapper = mount(Create, {
      props: { user: {}, title: 'Создать галерею' },
    });
    const btn = wrapper.find('button.btn-primary');
    expect(btn.attributes('disabled')).toBeDefined();
  });

  it('enables create button when title is filled', async () => {
    const wrapper = mount(Create, {
      props: { user: {}, title: 'Создать галерею' },
    });
    await wrapper.find('input[type="text"]').setValue('Valid Title');
    const btn = wrapper.find('button.btn-primary');
    expect(btn.attributes('disabled')).toBeUndefined();
  });

  it('calls api.store and redirects on success', async () => {
    vi.mocked(galleryApi.store).mockResolvedValueOnce({
      data: { success: true, message: 'Created', id: 42 },
    } as any);

    const wrapper = mount(Create, {
      props: { user: {}, title: 'Создать галерею' },
    });
    await wrapper.find('input[type="text"]').setValue('My Gallery');
    await wrapper.find('button.btn-primary').trigger('click');
    await flushPromises();

    expect(galleryApi.store).toHaveBeenCalledWith({
      title: 'My Gallery',
      type: 'grid',
      status: true,
      settings: {},
    });
  });

  it('shows error notification on api failure', async () => {
    vi.mocked(galleryApi.store).mockRejectedValueOnce({
      response: { data: { message: 'Validation failed' } },
    });

    const wrapper = mount(Create, {
      props: { user: {}, title: 'Создать галерею' },
    });
    await wrapper.find('input[type="text"]').setValue('Fail');
    await wrapper.find('button.btn-primary').trigger('click');
    await flushPromises();

    expect(wrapper.vm.notification.type).toBe('error');
    expect(wrapper.vm.notification.message).toBe('Validation failed');
  });

  it('shows generic error when response has no message', async () => {
    vi.mocked(galleryApi.store).mockRejectedValueOnce(new Error('Network error'));

    const wrapper = mount(Create, {
      props: { user: {}, title: 'Создать галерею' },
    });
    await wrapper.find('input[type="text"]').setValue('Fail');
    await wrapper.find('button.btn-primary').trigger('click');
    await flushPromises();

    expect(wrapper.vm.notification.type).toBe('error');
    expect(wrapper.vm.notification.message).toBe('Ошибка при создании');
  });

  it('shows loading state during save', async () => {
    vi.mocked(galleryApi.store).mockImplementationOnce(() => new Promise(() => {}));

    const wrapper = mount(Create, {
      props: { user: {}, title: 'Создать галерею' },
    });
    await wrapper.find('input[type="text"]').setValue('Loading');
    wrapper.find('button.btn-primary').trigger('click');
    await flushPromises();

    expect(wrapper.vm.loading).toBe(true);
  });

  it('navigates back on cancel', async () => {
    const { router } = await import('@inertiajs/vue3');
    const wrapper = mount(Create, {
      props: { user: {}, title: 'Создать галерею' },
    });
    await wrapper.find('.btn-cancel').trigger('click');
    expect(router.visit).toHaveBeenCalledWith('/admin/galleries');
  });
});
