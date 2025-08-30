<script setup lang="ts">
import { createZodPlugin } from '@formkit/zod';

const { t } = useI18n();
definePageMeta({
  middleware: ['auth'],
  title: 'Account'
});
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
  async (formData) => {
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
</script>

<template>
  <div>
    <h2>{{ t('navbar.account').toUpperCase() }}</h2>
    <div v-if="authStore.user">
      <p>
        <strong>{{ t('register.name').toUpperCase() }}:</strong>
        {{ authStore.user.name }}
      </p>
      <p>
        <strong>{{ t('register.username').toUpperCase() }}:</strong>
        {{ authStore.user.username }}
      </p>
      <p>
        <strong>{{ t('register.email').toUpperCase() }}:</strong>
        {{ authStore.user.email }}
      </p>

      <section class="sessions-section">
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
        <h3>{{ t('account.sessions').toUpperCase() }}</h3>
        <div v-if="loadingSessions">
          {{ t('account.loading').toUpperCase() }}
        </div>
        <div v-else-if="errorSessions">{{ errorSessions.toUpperCase() }}</div>
        <ul v-else>
          <li
            v-for="session in sessions.filter((session: { isCurrentDevice: boolean }) => !session.isCurrentDevice)"
            :key="session.ip + session.lastActive"
          >
            <span>
              {{ session.browser }} ({{ session.platform }}) -
              {{ session.ip }} - {{ t('account.last_active').toUpperCase() }}:
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
    <div v-else>
      <p>{{ t('account.loading').toUpperCase() }}</p>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.sessions-section {
  margin-top: 2rem;
}
.delete-sessions-form {
  margin-top: 1rem;
  max-width: 400px;
}
.formkit-message.delete-status {
  margin-top: 0.5rem;
}
</style>
<style lang="scss" scoped>
.password-update-form {
  margin-top: 2rem;
  max-width: 400px;
  width: 100%;
}
</style>
