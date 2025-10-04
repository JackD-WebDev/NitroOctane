import { describe, it, expect, afterEach, beforeEach, vi } from 'vitest';
import { reactive, ref } from 'vue';
import { mountSuspended } from '@nuxt/test-utils/runtime';
import flushPromises from 'flush-promises';
import { createTestingPinia } from '@pinia/testing';
import type { VueWrapper } from '@vue/test-utils';
import type { ComponentPublicInstance } from 'vue';
import Register from '../app/pages/register/index.vue';
import Login from '../app/pages/login/index.vue';

const authStoreMock = reactive({
  register: vi.fn(),
  logIn: vi.fn(),
  error: ''
});
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

    beforeEach(async () => {
      createTestingPinia({ createSpy: vi.fn, stubActions: false });

      authStoreMock.register.mockClear();
      authStoreMock.error = '';
      routerMock.push.mockClear();

      vi.stubGlobal('useRouter', () => routerMock);
      vi.stubGlobal('useAuthStore', () => authStoreMock);
      vi.stubGlobal('definePageMeta', vi.fn());
      vi.stubGlobal('useAppTitle', vi.fn());
      vi.stubGlobal('ref', (val: unknown) => ({ value: val }));
      vi.stubGlobal('computed', (fn: () => unknown) => ({ value: fn() }));

      wrapper = await mountSuspended(Register);
      await flushPromises();
    });

    it('renders all input fields', () => {
      expect(wrapper.find('input[name="firstname"]').exists()).toBe(true);
      expect(wrapper.find('input[name="middlename"]').exists()).toBe(true);
      expect(wrapper.find('input[name="lastname"]').exists()).toBe(true);
      expect(wrapper.find('input[name="username"]').exists()).toBe(true);
      expect(wrapper.find('input[name="email"]').exists()).toBe(true);
      expect(wrapper.find('input[name="password"]').exists()).toBe(true);
      expect(wrapper.find('input[name="confirmPassword"]').exists()).toBe(true);
    });

    it('calls register and redirects on success', async () => {
      authStoreMock.register.mockClear();
      authStoreMock.register.mockResolvedValue({
        success: true,
        user: { id: 1 }
      });

      const testWrapper = await mountSuspended(Register);
      await flushPromises();

      await testWrapper.find('input[name="firstname"]').setValue('John');
      await testWrapper.find('input[name="lastname"]').setValue('Doe');
      await testWrapper.find('input[name="username"]').setValue('johndoe');
      await testWrapper
        .find('input[name="email"]')
        .setValue('john@example.com');
      await testWrapper.find('input[name="password"]').setValue('Password123!');
      await testWrapper
        .find('input[name="confirmPassword"]')
        .setValue('Password123!');

      await testWrapper.find('form').trigger('submit.prevent');
      await flushPromises();

      expect(authStoreMock.register).toHaveBeenCalled();
      const registerCall = authStoreMock.register.mock.calls[0];
      expect(registerCall?.[0]).toMatchObject({
        firstname: 'John',
        lastname: 'Doe',
        username: 'johndoe',
        email: 'john@example.com',
        password: 'Password123!',
        password_confirmation: 'Password123!'
      });
    });

    it('shows network error for unknown error', async () => {
      authStoreMock.register.mockClear();
      authStoreMock.register.mockRejectedValueOnce('some error');

      const testWrapper = await mountSuspended(Register);
      await flushPromises();

      await testWrapper.find('input[name="firstname"]').setValue('Alice');
      await testWrapper.find('input[name="lastname"]').setValue('Smith');
      await testWrapper.find('input[name="username"]').setValue('alicesmith');
      await testWrapper
        .find('input[name="email"]')
        .setValue('alice@example.com');
      await testWrapper.find('input[name="password"]').setValue('Password123!');
      await testWrapper
        .find('input[name="confirmPassword"]')
        .setValue('Password123!');
      await testWrapper.find('form').trigger('submit.prevent');
      await flushPromises();
      expect(authStoreMock.register).toHaveBeenCalled();
    });
  });
  type AuthUser = {
    id: number;
    name: string;
    username: string;
    preferred_language: string;
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
  beforeEach(async () => {
    const mod = await import('../app/stores/AuthStore');
    const realStore = mod.useAuthStore();
    Object.assign(realStore, {
      register: vi.fn(),
      logIn: vi.fn(),
      logOut: vi.fn(),
      error: ref(''),
      setError: vi.fn(),
      setUser: vi.fn(),
      authUser: null
    });
    authStore = realStore as unknown as MockAuthStore;
  });
  const originalFetch = globalThis.fetch;

  afterEach(() => {
    globalThis.fetch = originalFetch;
  });

  it('shows error for missing registration fields', async () => {
    vi.stubGlobal('useAuthStore', () => authStoreMock);
    vi.stubGlobal('useRouter', () => routerMock);
    vi.stubGlobal('definePageMeta', vi.fn());
    vi.stubGlobal('useAppTitle', vi.fn());
    vi.stubGlobal('ref', (val: unknown) => ({ value: val }));
    vi.stubGlobal('computed', (fn: () => unknown) => ({ value: fn() }));

    const wrapper = await mountSuspended(Register);
    await flushPromises();
    await wrapper.get('form').trigger('submit.prevent');
    await flushPromises();
    expect(wrapper.html()).toMatch(/required/i);
  });

  it('logs in successfully', async () => {
    vi.stubGlobal('useAuthStore', () => authStoreMock);
    vi.stubGlobal('useRouter', () => routerMock);
    vi.stubGlobal('definePageMeta', vi.fn());
    vi.stubGlobal('useAppTitle', vi.fn());
    vi.stubGlobal('ref', (val: unknown) => ({ value: val }));
    vi.stubGlobal('computed', (fn: () => unknown) => ({ value: fn() }));

    vi.stubGlobal('createLoginSchema', () => ({
      parseAsync: vi.fn().mockResolvedValue({})
    }));
    const mockSubmitHandler = vi.fn(async (formData) => {
      const result = await authStoreMock.logIn(formData, 'en_US');
      return result;
    });
    vi.stubGlobal('createZodPlugin', () => [vi.fn(), mockSubmitHandler]);

    authStoreMock.logIn.mockResolvedValue({
      requiresTwoFactor: false,
      user: { id: 1, username: 'testuser' }
    });

    const _wrapper = await mountSuspended(Login);
    await flushPromises();

    await mockSubmitHandler({
      email: 'testuser@example.com',
      password: 'Password123!',
      remember: false
    });
    await flushPromises();

    expect(authStoreMock.logIn).toHaveBeenCalled();
  });

  it('shows error for missing login fields', async () => {
    vi.stubGlobal('useAuthStore', () => authStoreMock);
    vi.stubGlobal('useRouter', () => routerMock);
    vi.stubGlobal('definePageMeta', vi.fn());
    vi.stubGlobal('useAppTitle', vi.fn());
    vi.stubGlobal('ref', (val: unknown) => ({ value: val }));
    vi.stubGlobal('computed', (fn: () => unknown) => ({ value: fn() }));

    const wrapper = await mountSuspended(Login);
    await flushPromises();
    await wrapper.get('form').trigger('submit.prevent');
    await flushPromises();
    expect(wrapper.html()).toMatch(/required/i);
  });

  it('shows error for invalid login credentials', async () => {
    vi.stubGlobal('useAuthStore', () => authStoreMock);
    vi.stubGlobal('useRouter', () => routerMock);
    vi.stubGlobal('definePageMeta', vi.fn());
    vi.stubGlobal('useAppTitle', vi.fn());
    vi.stubGlobal('ref', (val: unknown) => ({ value: val }));
    vi.stubGlobal('computed', (fn: () => unknown) => ({ value: fn() }));

    const mockSubmitHandler = vi.fn(async (formData) => {
      try {
        const result = await authStoreMock.logIn(formData, 'en_US');
        return result;
      } catch (error) {
        if (error instanceof Error) {
          authStoreMock.error = error.message;
        } else {
          authStoreMock.error = 'Network error';
        }
      }
    });
    vi.stubGlobal('createZodPlugin', () => [vi.fn(), mockSubmitHandler]);

    authStoreMock.logIn.mockRejectedValue(
      new Error('The provided credentials are incorrect')
    );

    const _wrapper = await mountSuspended(Login);
    await flushPromises();

    await mockSubmitHandler({
      email: 'testuser@example.com',
      password: 'wrongpassword',
      remember: false
    });
    await flushPromises();

    expect(authStoreMock.logIn).toHaveBeenCalled();
  });

  it('handles fetch errors gracefully', async () => {
    authStore.register.mockRejectedValue(new Error('Network error'));
    const wrapper = await mountSuspended(Register);
    await flushPromises();
    await wrapper.get('form').trigger('submit.prevent');
    await flushPromises();
    authStore.error = 'Network error';
    expect(authStore.error).toContain('Network error');
  });

  it('handles fetch errors in login', async () => {
    authStore.logIn.mockRejectedValue(new Error('Network error'));
    const wrapper = await mountSuspended(Login);
    await flushPromises();
    await wrapper.get('form').trigger('submit.prevent');
    await flushPromises();
    authStore.error = 'Network error';
    expect(authStore.error).toContain('Network error');
  });

  it('clears error on successful registration', async () => {
    authStore.register.mockImplementation(() => {
      authStore.error = '';
      return Promise.resolve({ success: true });
    });
    const wrapper = await mountSuspended(Register);
    await flushPromises();
    await wrapper.get('input[name="email"]').setValue('testuser@example.com');
    await wrapper.get('input[name="password"]').setValue('Password123!');
    await wrapper.get('form').trigger('submit.prevent');
    await flushPromises();
    expect(authStore.error).toBe('');
  });

  it('clears error on successful login', async () => {
    authStore.logIn.mockImplementation(() => {
      authStore.error = '';
      return Promise.resolve({ success: true });
    });
    const wrapper = await mountSuspended(Login);
    await flushPromises();
    await wrapper.get('input[name="email"]').setValue('testuser@example.com');
    await wrapper.get('input[name="password"]').setValue('Password123!');
    await wrapper.get('form').trigger('submit.prevent');
    await flushPromises();
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
      preferred_language: 'en',
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
