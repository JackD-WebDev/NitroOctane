<script setup lang="ts">
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

async function fetchSessions() {
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
}

async function deleteOtherSessions() {
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
}

onMounted(() => {
  // Only fetch sessions if user is authenticated
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
        <strong>{{ t('register.username').toUpperCase() }}:</strong>
        {{ authStore.user.username }}
      </p>
      <p>
        <strong>{{ t('register.email').toUpperCase() }}:</strong>
        {{ authStore.user.email }}
      </p>

      <section class="sessions-section">
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
                style="margin-top: 1rem"
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
