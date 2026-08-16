import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { useGalleryNotifications } from '@/modules/GalleryManager/ui/composables/useGalleryNotifications';

const TestComponent = {
  setup() {
    return useGalleryNotifications();
  },
  template: '<div></div>',
};

describe('useGalleryNotifications', () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it('initializes with hidden notification', () => {
    const wrapper = mount(TestComponent);

    expect(wrapper.vm.notification.show).toBe(false);
    expect(wrapper.vm.notification.message).toBe('');
    expect(wrapper.vm.notification.type).toBe('success');
  });

  it('shows success notification by default', () => {
    const wrapper = mount(TestComponent);

    wrapper.vm.showNotification('Operation completed');

    expect(wrapper.vm.notification.show).toBe(true);
    expect(wrapper.vm.notification.message).toBe('Operation completed');
    expect(wrapper.vm.notification.type).toBe('success');
  });

  it('shows error notification', () => {
    const wrapper = mount(TestComponent);

    wrapper.vm.showNotification('Something went wrong', 'error');

    expect(wrapper.vm.notification.type).toBe('error');
  });

  it('hides notification after 5000ms', () => {
    const wrapper = mount(TestComponent);

    wrapper.vm.showNotification('Auto hide test');
    expect(wrapper.vm.notification.show).toBe(true);

    vi.advanceTimersByTime(5000);
    expect(wrapper.vm.notification.show).toBe(false);
  });

  it('hides notification manually', () => {
    const wrapper = mount(TestComponent);

    wrapper.vm.showNotification('Manual hide test');
    wrapper.vm.hideNotification();

    expect(wrapper.vm.notification.show).toBe(false);
  });

  it('resets timeout on consecutive showNotification calls', () => {
    const wrapper = mount(TestComponent);

    wrapper.vm.showNotification('First');
    vi.advanceTimersByTime(3000);

    wrapper.vm.showNotification('Second');
    vi.advanceTimersByTime(3000);

    expect(wrapper.vm.notification.show).toBe(true);
    expect(wrapper.vm.notification.message).toBe('Second');

    vi.advanceTimersByTime(2000);
    expect(wrapper.vm.notification.show).toBe(false);
  });

  it('does not throw when hideNotification called without active timeout', () => {
    const wrapper = mount(TestComponent);

    expect(() => wrapper.vm.hideNotification()).not.toThrow();
    expect(wrapper.vm.notification.show).toBe(false);
  });
});
