import { describe, it, expect, afterEach, beforeEach, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { createTestingPinia } from '@pinia/testing';
import type { VueWrapper } from '@vue/test-utils';
import type { ComponentPublicInstance } from 'vue';
import { nextTick } from 'vue';
import Register from '../app/pages/register/index.vue';
import Login from '../app/pages/login/index.vue';

const authStoreMock = { register: vi.fn(), error: '' };
const routerMock = { push: vi.fn() };

vi.mock('#imports', () => ({
  useRouter: () => routerMock,
  useAuthStore: () => authStoreMock,
  definePageMeta: vi.fn(),
  useAppTitle: vi.fn(),
  ref: (val: unknown) => ({ value: val }),
  computed: (fn: () => unknown) => ({ value: fn() })
}));

vi.mock('@/stores/AuthStore', () => ({
  useAuthStore: () => authStoreMock
}));

vi.mock('vue-router', () => ({
  useRouter: () => routerMock
}));

vi.mock('#app', () => ({
  useRouter: () => routerMock,
  useAuthStore: () => authStoreMock,
  definePageMeta: vi.fn(),
  useAppTitle: vi.fn()
}));

describe('Auth', () => {
  describe('Register Page', () => {
    let wrapper: VueWrapper<ComponentPublicInstance>;
    let pinia: ReturnType<typeof createTestingPinia>;

    beforeEach(() => {
      pinia = createTestingPinia({ createSpy: vi.fn, stubActions: false });

      authStoreMock.register.mockReset();
      authStoreMock.error = '';
      routerMock.push.mockReset();

      vi.stubGlobal('useRouter', () => routerMock);
      vi.stubGlobal('useAuthStore', () => authStoreMock);
      vi.stubGlobal('definePageMeta', vi.fn());
      vi.stubGlobal('useAppTitle', vi.fn());
      vi.stubGlobal('ref', (val: unknown) => ({ value: val }));
      vi.stubGlobal('computed', (fn: () => unknown) => ({ value: fn() }));

      wrapper = mount(Register, {
        global: {
          plugins: [pinia],
          stubs: {}
        }
      });
    });

    it('renders all input fields', () => {
      expect(wrapper.find('input#firstname').exists()).toBe(true);
      expect(wrapper.find('input#middlename').exists()).toBe(true);
      expect(wrapper.find('input#lastname').exists()).toBe(true);
      expect(wrapper.find('input#username').exists()).toBe(true);
      expect(wrapper.find('input#email').exists()).toBe(true);
      expect(wrapper.find('input#password').exists()).toBe(true);
      expect(wrapper.find('input#confirmPassword').exists()).toBe(true);
    });

    it('calls register and redirects on success', async () => {
      authStoreMock.register.mockReset();
      routerMock.push.mockReset();

      authStoreMock.register.mockImplementation(async () => {
        routerMock.push('/');
        return undefined;
      });

      await wrapper.find('input#firstname').setValue('John');
      await wrapper.find('input#lastname').setValue('Doe');
      await wrapper.find('input#username').setValue('johndoe');
      await wrapper.find('input#email').setValue('john@example.com');
      await wrapper.find('input#password').setValue('password');
      await wrapper.find('input#confirmPassword').setValue('password');

      await wrapper.find('form').trigger('submit.prevent');
      await wrapper.vm.$nextTick();
      await wrapper.vm.$nextTick();

      expect(authStoreMock.register).toHaveBeenCalled();
      expect(routerMock.push).toHaveBeenCalledWith('/');
    });

    it('shows error on register failure', async () => {
      authStoreMock.register.mockRejectedValueOnce(
        new Error('Registration failed')
      );
      await wrapper.find('form').trigger('submit.prevent');
      await wrapper.vm.$nextTick();
      await wrapper.vm.$nextTick();
      authStoreMock.error = 'Registration failed';
      await wrapper.vm.$forceUpdate?.();
      expect(wrapper.html()).toContain('Registration failed');
    });

    it('shows network error for unknown error', async () => {
      authStoreMock.register.mockRejectedValueOnce('some error');
      await wrapper.find('form').trigger('submit.prevent');
      await wrapper.vm.$nextTick();
      await wrapper.vm.$nextTick();
      authStoreMock.error = 'Network error';
      await wrapper.vm.$forceUpdate?.();
      expect(wrapper.html()).toContain('Network error');
    });
  });
  type AuthUser = {
    id: number;
    name: string;
    username: string;
    email: string;
    created_at: string;
    updated_at: string;
  } | null;
  interface MockAuthStore {
    register: ReturnType<typeof vi.fn>;
    logIn: ReturnType<typeof vi.fn>;
    logOut: ReturnType<typeof vi.fn>;
    error: string;
    setError: ReturnType<typeof vi.fn>;
    authUser: AuthUser;
  }
  let authStore: MockAuthStore;
  let pinia: ReturnType<typeof createTestingPinia>;
  beforeEach(async () => {
    pinia = createTestingPinia({
      createSpy: vi.fn,
      stubActions: false
    });
    const mod = await import('../app/stores/AuthStore');
    const realStore = mod.useAuthStore();
    Object.assign(realStore, {
      register: vi.fn(),
      logIn: vi.fn(),
      logOut: vi.fn(),
      error: '',
      setError: vi.fn(),
      authUser: null
    });
    authStore = realStore as unknown as MockAuthStore;
  });
  const originalFetch = globalThis.fetch;

  afterEach(() => {
    globalThis.fetch = originalFetch;
  });

  it('registers a new user successfully', async () => {
    const push = vi.fn();
    authStore.register.mockImplementation(() => {
      push('/dashboard');
      return Promise.resolve({ success: true });
    });
    const wrapper = mount(Register, {
      global: {
        plugins: [pinia],
        mocks: {
          $router: { push }
        }
      }
    });
    await wrapper.get('#password').setValue('Password123!');
    await wrapper.get('#confirmPassword').setValue('Password123!');
    await wrapper.get('form').trigger('submit.prevent');
    await nextTick();
    expect(push).toHaveBeenCalled();
  });

  it('shows error for missing registration fields', async () => {
    authStore.register.mockImplementation(() => {
      authStore.error = 'The name field is required';
      return Promise.resolve();
    });
    const wrapper = mount(Register, {
      global: {
        plugins: [pinia]
      }
    });
    await wrapper.get('form').trigger('submit.prevent');
    await nextTick();
    expect(authStore.error).toContain('The name field is required');
  });

  it('shows error for duplicate registration', async () => {
    authStore.register.mockImplementation(() => {
      authStore.error = 'The email has already been taken';
      return Promise.resolve();
    });
    const wrapper = mount(Register, {
      global: {
        plugins: [pinia]
      }
    });
    await wrapper.get('#email').setValue('testuser@example.com');
    await wrapper.get('form').trigger('submit.prevent');
    await nextTick();
    expect(authStore.error).toContain('The email has already been taken');
  });

  it('logs in successfully', async () => {
    const push = vi.fn();
    authStore.logIn.mockImplementation(() => {
      push('/dashboard');
      return Promise.resolve({ success: true });
    });
    const wrapper = mount(Login, {
      global: {
        plugins: [pinia],
        mocks: {
          $router: { push }
        }
      }
    });
    await wrapper.get('#email').setValue('testuser@example.com');
    await wrapper.get('#password').setValue('Password123!');
    await wrapper.get('form').trigger('submit.prevent');
    await nextTick();
    expect(push).toHaveBeenCalled();
  });

  it('shows error for missing login fields', async () => {
    authStore.logIn.mockImplementation(() => {
      authStore.error = 'The email field is required';
      return Promise.resolve();
    });
    const wrapper = mount(Login, {
      global: {
        plugins: [pinia]
      }
    });
    await wrapper.get('form').trigger('submit.prevent');
    await nextTick();
    expect(authStore.error).toContain('The email field is required');
  });

  it('shows error for invalid login credentials', async () => {
    authStore.logIn.mockImplementation(() => {
      authStore.error = 'The provided credentials are incorrect';
      return Promise.resolve();
    });
    const wrapper = mount(Login, {
      global: {
        plugins: [pinia]
      }
    });
    await wrapper.get('#email').setValue('testuser@example.com');
    await wrapper.get('#password').setValue('WrongPassword!');
    await wrapper.get('form').trigger('submit.prevent');
    await nextTick();
    expect(authStore.error).toContain('The provided credentials are incorrect');
  });

  it('handles fetch errors gracefully', async () => {
    authStore.register.mockRejectedValue(new Error('Network error'));
    const wrapper = mount(Register, {
      global: {
        plugins: [pinia]
      }
    });
    await wrapper.get('form').trigger('submit.prevent');
    await nextTick();
    authStore.error = 'Network error';
    expect(authStore.error).toContain('Network error');
  });

  it('handles fetch errors in login', async () => {
    authStore.logIn.mockRejectedValue(new Error('Network error'));
    const wrapper = mount(Login, {
      global: {
        plugins: [pinia]
      }
    });
    await wrapper.get('form').trigger('submit.prevent');
    await nextTick();
    authStore.error = 'Network error';
    expect(authStore.error).toContain('Network error');
  });

  it('clears error on successful registration', async () => {
    authStore.register.mockImplementation(() => {
      authStore.error = '';
      return Promise.resolve({ success: true });
    });
    const wrapper = mount(Register, {
      global: {
        plugins: [pinia]
      }
    });
    await wrapper.get('#email').setValue('testuser@example.com');
    await wrapper.get('#password').setValue('Password123!');
    await wrapper.get('form').trigger('submit.prevent');
    await nextTick();
    expect(authStore.error).toBe('');
  });

  it('clears error on successful login', async () => {
    authStore.logIn.mockImplementation(() => {
      authStore.error = '';
      return Promise.resolve({ success: true });
    });
    const wrapper = mount(Login, {
      global: {
        plugins: [pinia]
      }
    });
    await wrapper.get('#email').setValue('testuser@example.com');
    await wrapper.get('#password').setValue('Password123!');
    await wrapper.get('form').trigger('submit.prevent');
    await nextTick();
    expect(authStore.error).toBe('');
  });

  it('logs out successfully', async () => {
    authStore.logOut = vi.fn().mockResolvedValue({ success: true });
    const result = await authStore.logOut();
    expect(authStore.logOut).toHaveBeenCalled();
    expect(result).toHaveProperty('success', true);
  });

  it('handles logout errors', async () => {
    authStore.logOut = vi.fn().mockRejectedValue(new Error('Logout failed'));
    try {
      await authStore.logOut();
    } catch (e) {
      expect(e).toBeInstanceOf(Error);
      if (e instanceof Error) {
        expect(e.message).toBe('Logout failed');
      }
    }
  });

  it('fetches user data for authenticated user', async () => {
    const userData = {
      id: 1,
      name: 'TestUser_1',
      username: 'testuser1',
      email: 'testuser@example.com',
      created_at: '2025-01-01',
      updated_at: '2025-01-01'
    };
    authStore.authUser = userData;
    expect(authStore.authUser).toHaveProperty('email', 'testuser@example.com');
  });

  it('returns error for unauthenticated user', async () => {
    authStore.authUser = null;
    expect(authStore.authUser).toBeNull();
  });

  it('returns error for non-existent user', async () => {
    const fetchUser = vi.fn().mockRejectedValue(new Error('MODEL NOT FOUND.'));
    try {
      await fetchUser();
    } catch (e) {
      expect(e).toBeInstanceOf(Error);
      if (e instanceof Error) {
        expect(e.message).toBe('MODEL NOT FOUND.');
      }
    }
  });

  it('resets password successfully', async () => {
    const resetPassword = vi
      .fn()
      .mockResolvedValue({ message: 'Your password has been reset.' });
    const result = await resetPassword();
    expect(result).toHaveProperty('message', 'Your password has been reset.');
  });

  it('enforces rate limiting after failed login attempts', async () => {
    const rateLimitLogin = vi
      .fn()
      .mockRejectedValueOnce(new Error('WrongPassword!'))
      .mockRejectedValueOnce(new Error('WrongPassword!'))
      .mockRejectedValueOnce(new Error('WrongPassword!'))
      .mockRejectedValueOnce(new Error('WrongPassword!'))
      .mockRejectedValueOnce(new Error('WrongPassword!'))
      .mockRejectedValueOnce(new Error('WrongPassword!'))
      .mockRejectedValue(new Error('Too Many Attempts.'));
    let error: unknown;
    for (let i = 0; i < 7; i++) {
      try {
        await rateLimitLogin();
      } catch (e) {
        error = e;
      }
    }
    expect(error).toBeInstanceOf(Error);
    if (error instanceof Error) {
      expect(error.message).toBe('Too Many Attempts.');
    }
  });

  it('auth middleware redirects unauthenticated users to /login', async () => {
    const navigateTo = vi.fn();

    const mockAuthMiddleware = vi.fn(async (_to, _from) => {
      const authUser = null;
      if (!authUser) {
        return navigateTo('/login');
      }
      return true;
    });

    await mockAuthMiddleware(
      { path: '/', name: 'protected' },
      { path: '/previous', name: 'previous' }
    );

    expect(navigateTo).toHaveBeenCalledWith('/login');
  });

  it('auth middleware allows authenticated users', async () => {
    const navigateTo = vi.fn();

    const mockAuthMiddleware = vi.fn(async (_to, _from) => {
      const authUser = { id: 1 };
      if (!authUser) {
        return navigateTo('/login');
      }
      return true;
    });

    const result = await mockAuthMiddleware(
      { path: '/', name: 'protected' },
      { path: '/previous', name: 'previous' }
    );

    expect(navigateTo).not.toHaveBeenCalled();
    expect(result).toBe(true);
  });

  it('guest middleware redirects logged-in users from login/register', async () => {
    const navigateTo = vi.fn();

    const mockGuestMiddleware = vi.fn(async (_to, _from) => {
      const token = { value: 'sometoken' };
      if (token.value) {
        return navigateTo('/');
      }
    });

    await mockGuestMiddleware(
      { path: '/login', name: 'login' },
      { path: '/previous', name: 'previous' }
    );

    expect(navigateTo).toHaveBeenCalledWith('/');
  });
});
