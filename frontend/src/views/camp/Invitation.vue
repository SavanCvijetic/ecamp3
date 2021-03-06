<template>
  <auth-container v-if="ready">
    <div v-if="invitationFound">
      <h1 v-if="userDisplayName" class="display-1">
        {{ $tc('components.invitation.userWelcome') }} {{ userDisplayName }}
      </h1>
      <h1 class="display-1">
        {{ $tc('components.invitation.title') }} {{ invitation().campTitle }}
      </h1>

      <v-spacer />
      <div v-if="userDisplayName">
        <v-btn v-if="!invitation().userAlreadyInCamp" color="primary"
               x-large
               class="my-4" block
               @click="acceptInvitation">
          {{ $tc('components.invitation.acceptCurrentAuth') }}
        </v-btn>
        <div v-else>
          <v-alert type="warning">
            {{ $tc('components.invitation.userAlreadyInCamp') }}
          </v-alert>
          <v-spacer />
          <v-btn color="primary"
                 x-large
                 class="my-4" block
                 :to="campLink">
            {{ $tc('components.invitation.openCamp') }}
          </v-btn>
        </div>
        <v-btn color="primary"
               x-large
               class="my-4" block
               @click="useAnotherAccount">
          {{ $tc('components.invitation.useOtherAuth') }}
        </v-btn>
      </div>
      <div v-else>
        <v-btn color="primary"
               x-large
               class="my-4" block
               :to="loginLink">
          {{ $tc('components.invitation.login') }}
        </v-btn>
        <v-btn color="primary"
               x-large
               class="my-4" block
               :to="{ name: 'register' }">
          {{ $tc('components.invitation.register') }}
        </v-btn>
      </div>
      <v-btn color="red"
             x-large
             class="my-4" block
             @click="rejectInvitation">
        {{ $tc('components.invitation.reject') }}
      </v-btn>
    </div>
    <v-alert v-else-if="invitationFound === false" type="error">
      {{ $tc('components.invitation.notFound') }}
    </v-alert>
    <v-btn color="primary"
           x-large
           class="my-4" block
           :to="{ name: 'home' }">
      {{ $tc('components.invitation.backToHome') }}
    </v-btn>
  </auth-container>
</template>

<script>
import AuthContainer from '@/components/layout/AuthContainer'
import { loginRoute } from '@/router'

export default {
  name: 'Invitation',
  components: { AuthContainer },
  props: {
    invitation: { type: Function, required: true }
  },
  data: () => ({
    invitationFound: undefined
  }),
  computed: {
    campLink () {
      return {
        name: 'camp/program',
        params: { campId: this.invitation().campId }
      }
    },
    loginLink () {
      return loginRoute(this.$route.fullPath)
    },
    ready () {
      return this.invitationFound !== undefined
    },
    userDisplayName () {
      return this.invitation().userDisplayName
    }
  },
  mounted () {
    this.api.reload(this.invitation())
      .then(
        () => { this.invitationFound = true },
        () => { this.invitationFound = false }
      )
  },
  methods: {
    useAnotherAccount () {
      // Remember the login link for after we are logged out
      const loginLink = this.loginLink
      this.$auth.logout().then(__ => this.$router.push(loginLink))
    },
    acceptInvitation () {
      this.api.href(this.api.get(), 'invitation', {
        action: 'accept',
        inviteKey: this.$route.params.inviteKey
      }).then(postUrl => this.api.post(postUrl, {}))
        .then(
          _ => { this.$router.push(this.campLink) },
          () => { this.$router.push({ name: 'invitationUpdateError' }) }
        )
    },
    rejectInvitation () {
      this.api.href(this.api.get(), 'invitation', {
        action: 'reject',
        inviteKey: this.$route.params.inviteKey
      }).then(postUrl => this.api.post(postUrl, {}))
        .then(
          _ => { this.$router.push({ name: 'invitationRejected' }) },
          () => { this.$router.push({ name: 'invitationUpdateError' }) }
        )
    }
  }
}
</script>

<style scoped>

</style>
