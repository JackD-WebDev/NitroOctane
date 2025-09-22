<script setup lang="ts">
import { createZodPlugin } from '@formkit/zod';

const { t } = useI18n();
definePageMeta({ middleware: ['auth', 'verified'], title: 'Account' });
useAppTitle(t('navbar.account'));
const authStore = useAuthStore();
const sessions = ref<SessionResponse['data']>([]);
const loadingSessions = ref(true);
const errorSessions = ref('');
const password = ref('');
const deleteStatus = ref('');
const deleting = ref(false);
const passwordUpdateStatus = ref('');
const passwordUpdating = ref(false);
const passwordUpdateError = ref('');
const passwordUpdateSchema = createPasswordUpdateSchema();
const [passwordZodPlugin, passwordSubmitHandler] = createZodPlugin(
  passwordUpdateSchema,
  async (formData: {
    current_password: string;
    password: string;
    password_confirmation: string;
  }) => {
    passwordUpdating.value = true;
    passwordUpdateStatus.value = '';
    passwordUpdateError.value = '';
    try {
      const response: PasswordUpdateResponse = await useApi('user/password', {
        method: 'PUT',
        body: JSON.stringify(formData)
      });
      if (response.success) {
        passwordUpdateStatus.value = t('account.password_update_success');
      } else {
        passwordUpdateError.value =
          response.message || t('account.password_update_failed');
      }
    } catch {
      passwordUpdateError.value = t('account.password_update_failed');
    } finally {
      passwordUpdating.value = false;
    }
  }
);
const fetchSessions = async () => {
  loadingSessions.value = true;
  errorSessions.value = '';
  try {
    const sessionsResponse: SessionResponse = await useApi('/sessions');
    if (sessionsResponse.success && Array.isArray(sessionsResponse.data)) {
      sessions.value = sessionsResponse.data;
    } else {
      errorSessions.value =
        sessionsResponse.message || t('account.session_fetch_error');
    }
  } catch {
    errorSessions.value = t('account.session_fetch_error');
  } finally {
    loadingSessions.value = false;
  }
};
const deleteOtherSessions = async () => {
  if (!password.value) return;
  deleting.value = true;
  deleteStatus.value = '';
  try {
    const sessionsResponse: LogoutOtherSessionsResponse = await useApi(
      '/sessions',
      {
        method: 'DELETE',
        body: JSON.stringify({ password: password.value })
      }
    );
    if (sessionsResponse.success) {
      deleteStatus.value = t('account.sessions_deleted');
      await fetchSessions();
      password.value = '';
    } else {
      deleteStatus.value =
        sessionsResponse.message || t('account.delete_failed');
    }
  } catch {
    deleteStatus.value = t('account.delete_failed');
  } finally {
    deleting.value = false;
  }
};
onMounted(() => {
  if (authStore.user) {
    fetchSessions();
  }
});
type ExtendedUser = typeof authStore.user & {
  firstname?: string;
  middlename?: string;
  lastname?: string;
};
const userForm = reactive({
  firstname: (authStore.user as ExtendedUser)?.firstname || '',
  middlename: (authStore.user as ExtendedUser)?.middlename || '',
  lastname: (authStore.user as ExtendedUser)?.lastname || '',
  username: authStore.user?.username || '',
  email: authStore.user?.email || ''
});

const { uniqueChecks, onFieldInput, onFieldBlur, resetFieldState } =
  useUniqueFieldCheck({
    username: authStore.user?.username,
    email: authStore.user?.email
  });

watch(
  () => authStore.user,
  (user) => {
    userForm.firstname = (user as ExtendedUser)?.firstname || '';
    userForm.middlename = (user as ExtendedUser)?.middlename || '';
    userForm.lastname = (user as ExtendedUser)?.lastname || '';
    userForm.username = user?.username || '';
    userForm.email = user?.email || '';
  },
  { immediate: true }
);
const userUpdateStatus = ref('');
const userUpdateError = ref('');
const userUpdating = ref(false);
const userInfoSubmitHandler = async (formData: typeof userForm) => {
  userUpdating.value = true;
  userUpdateStatus.value = '';
  userUpdateError.value = '';
  try {
    const response: { success: boolean; message?: string } = await useApi(
      'user',
      {
        method: 'PUT',
        body: JSON.stringify(formData)
      }
    );
    if (response.success) {
      userUpdateStatus.value = t('account.user_update_success');
      if (authStore.user) Object.assign(authStore.user, formData);
    } else {
      userUpdateError.value =
        response.message || t('account.user_update_failed');
    }
  } catch {
    userUpdateError.value = t('account.user_update_failed');
  } finally {
    userUpdating.value = false;
  }
};

onUnmounted(() => {
  resetFieldState('username');
  resetFieldState('email');
});
</script>

<template>
  <div>
    <h2>{{ t('navbar.account').toUpperCase() }}</h2>
    <div v-if="authStore.user">
      <div class="flex-container-3">
        <div class="left-column">
          <section class="user-update-section">
            <h3>{{ t('account.update_user_info').toUpperCase() }}</h3>
            <FormKit
              type="form"
              :actions="false"
              :classes="{ form: 'user-update-form styled-form' }"
              @submit="userInfoSubmitHandler"
            >
              <div class="formkit-outer">
                <div class="formkit-wrapper">
                  <FormKit
                    v-model="userForm.firstname"
                    type="text"
                    name="firstname"
                    :label="t('register.firstname').toUpperCase()"
                    :placeholder="t('register.firstname')"
                  />
                </div>
                <div class="formkit-wrapper">
                  <FormKit
                    v-model="userForm.middlename"
                    type="text"
                    name="middlename"
                    :label="t('register.middlename').toUpperCase()"
                    :placeholder="t('register.middlename')"
                  />
                </div>
                <div class="formkit-wrapper">
                  <FormKit
                    v-model="userForm.lastname"
                    type="text"
                    name="lastname"
                    :label="t('register.lastname').toUpperCase()"
                    :placeholder="t('register.lastname')"
                  />
                </div>
                <div class="formkit-wrapper">
                  <FormKit
                    v-model="userForm.username"
                    type="text"
                    name="username"
                    :label="t('register.username').toUpperCase()"
                    :placeholder="t('register.username')"
                    @input="(val) => onFieldInput('username', val)"
                    @blur="onFieldBlur('username')"
                  />
                  <div class="field-feedback">
                    <small v-if="uniqueChecks.username.value.loading"
                      >{{ t('checking') }}...</small
                    >
                    <small
                      v-else-if="uniqueChecks.username.value.unique === false"
                      class="text-small-uppercase-error"
                      >{{ uniqueChecks.username.value.message }}</small
                    >
                    <small
                      v-else-if="uniqueChecks.username.value.unique === true"
                      class="text-small-uppercase-success"
                      >{{
                        t('register.validation.username_available', {
                          username: userForm.username || ''
                        })
                      }}</small
                    >
                  </div>
                </div>
                <div class="formkit-wrapper">
                  <FormKit
                    v-model="userForm.email"
                    type="email"
                    name="email"
                    :label="t('register.email').toUpperCase()"
                    :placeholder="t('register.email')"
                    @input="(val) => onFieldInput('email', val)"
                    @blur="onFieldBlur('email')"
                  />
                  <div class="field-feedback">
                    <small v-if="uniqueChecks.email.value.loading"
                      >{{ t('checking') }}...</small
                    >
                    <small
                      v-else-if="uniqueChecks.email.value.unique === false"
                      class="text-small-uppercase-error"
                      >{{ uniqueChecks.email.value.message }}</small
                    >
                    <small
                      v-else-if="uniqueChecks.email.value.unique === true"
                      class="text-small-uppercase-success"
                      >{{
                        t('register.validation.email_available', {
                          email: userForm.email || ''
                        })
                      }}</small
                    >
                  </div>
                </div>
                <div class="formkit-actions">
                  <FormKit type="submit" :disabled="userUpdating">
                    {{
                      userUpdating
                        ? t('account.updating').toUpperCase()
                        : t('account.update_user_info').toUpperCase()
                    }}
                  </FormKit>
                </div>
                <div v-if="userUpdateStatus" class="formkit-message">
                  {{ userUpdateStatus }}
                </div>
                <div v-if="userUpdateError" class="formkit-message">
                  {{ userUpdateError }}
                </div>
              </div>
            </FormKit>
          </section>
        </div>
        <div class="center-column">
          <section class="password-section">
            <h3>
              {{
                t('account.update_password')
                  ? t('account.update_password').toUpperCase()
                  : t('register.password').toUpperCase()
              }}
            </h3>
            <FormKit
              type="form"
              :plugins="[passwordZodPlugin]"
              :actions="false"
              :classes="{ form: 'password-update-form styled-form' }"
              @submit="passwordSubmitHandler"
            >
              <div class="formkit-outer">
                <div class="formkit-wrapper">
                  <FormKit
                    type="password"
                    name="current_password"
                    :label="
                      t('account.current_password')
                        ? t('account.current_password').toUpperCase()
                        : t('register.password').toUpperCase()
                    "
                    validation="required"
                    :placeholder="
                      t('account.current_password')
                        ? t('account.current_password')
                        : t('register.password')
                    "
                  />
                </div>
                <div class="formkit-wrapper">
                  <FormKit
                    type="password"
                    name="password"
                    :label="
                      t('account.new_password')
                        ? t('account.new_password').toUpperCase()
                        : t('register.password').toUpperCase()
                    "
                    validation="required|length:12"
                    :placeholder="
                      t('account.new_password')
                        ? t('account.new_password')
                        : t('register.password')
                    "
                  />
                </div>
                <div class="formkit-wrapper">
                  <FormKit
                    type="password"
                    name="password_confirmation"
                    :label="
                      t('account.confirm_new_password')
                        ? t('account.confirm_new_password').toUpperCase()
                        : t('register.confirm_password').toUpperCase()
                    "
                    validation="required"
                    :placeholder="
                      t('account.confirm_new_password')
                        ? t('account.confirm_new_password')
                        : t('register.confirm_password')
                    "
                  />
                </div>
                <div class="formkit-actions">
                  <FormKit type="submit" :disabled="passwordUpdating">
                    {{
                      passwordUpdating
                        ? t('account.updating')
                          ? t('account.updating').toUpperCase()
                          : t('account.deleting').toUpperCase()
                        : t('account.update_password')
                        ? t('account.update_password').toUpperCase()
                        : t('register.password').toUpperCase()
                    }}
                  </FormKit>
                </div>
                <div v-if="passwordUpdateStatus">
                  {{ passwordUpdateStatus }}
                </div>
                <div v-if="passwordUpdateError">
                  {{ passwordUpdateError }}
                </div>
              </div>
            </FormKit>
          </section>
          <section class="sessions-section">
            <h3>{{ t('account.sessions').toUpperCase() }}</h3>
            <div v-if="loadingSessions">
              {{ t('account.loading').toUpperCase() }}
            </div>
            <div v-else-if="errorSessions">
              {{ errorSessions.toUpperCase() }}
            </div>
            <ul v-else>
              <li
                v-for="session in sessions.filter((session: { isCurrentDevice: boolean }) => !session.isCurrentDevice)"
                :key="session.ip + session.lastActive"
              >
                <span>
                  {{ session.browser }} ({{ session.platform }}) -
                  {{ session.ip }} -
                  {{ t('account.last_active').toUpperCase() }}:
                  {{ session.lastActive }}
                </span>
              </li>
              <li
                v-if="sessions.filter((session: { isCurrentDevice: boolean }) => !session.isCurrentDevice).length === 0"
              >
                {{ t('account.no_other_sessions').toUpperCase() }}
              </li>
            </ul>
            <form
              class="delete-sessions-form styled-form"
              @submit.prevent="deleteOtherSessions"
            >
              <div class="formkit-outer">
                <div class="formkit-wrapper">
                  <label class="formkit-label">
                    {{ t('account.password').toUpperCase() }}
                    <div class="formkit-inner">
                      <input
                        v-model="password"
                        type="password"
                        class="formkit-input"
                        :placeholder="t('account.password')"
                        required
                      />
                    </div>
                  </label>
                </div>
                <div class="formkit-messages">
                  <div class="formkit-message confirm-password-helper">
                    {{ t('account.confirm_password_helper').toUpperCase() }}
                  </div>
                </div>
                <div class="formkit-actions">
                  <button
                    type="submit"
                    :disabled="deleting || !password"
                    class="formkit-input"
                  >
                    {{
                      deleting
                        ? t('account.deleting').toUpperCase()
                        : t('account.delete_other_sessions').toUpperCase()
                    }}
                  </button>
                </div>
                <div v-if="deleteStatus" class="formkit-message delete-status">
                  {{ deleteStatus.toUpperCase() }}
                </div>
              </div>
            </form>
            <div v-if="deleteStatus" class="delete-status">
              {{ deleteStatus.toUpperCase() }}
            </div>
          </section>
        </div>
        <div class="right-column">
          <TwoFactorPanel />
        </div>
      </div>
    </div>
    <div v-else>
      <p>{{ t('account.loading').toUpperCase() }}</p>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.flex-container-3 {
  display: flex;
  gap: 2rem;
  align-items: flex-start;
}
.left-column {
  flex: 1;
  min-width: 300px;
}
.center-column {
  flex: 1;
  min-width: 300px;
  padding-left: 4rem;
  margin-top: 0.5rem;
}
.right-column {
  flex: 1;
  min-width: 300px;
}
.sessions-section {
  margin-top: 0;
}
.password-section {
  margin-top: 0;
}
.delete-sessions-form {
  margin-top: 1rem;
  max-width: 400px;
}
.formkit-message.delete-status {
  margin-top: 0.5rem;
}
.password-update-form {
  margin-top: 2rem;
  max-width: 400px;
  width: 100%;
}
</style>
