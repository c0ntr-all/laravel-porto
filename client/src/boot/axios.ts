import { boot } from 'quasar/wrappers'
import axios, { AxiosInstance } from 'axios'
import { Notify } from 'quasar'
import {
  getAccessToken,
  getSessionEpoch,
  handleUnauthorized,
  setUnauthorizedHandler
} from 'src/services/auth-session'

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $axios: AxiosInstance;
    $api: AxiosInstance;
  }
}

declare module 'axios' {
  interface InternalAxiosRequestConfig {
    sessionEpoch?: number;
  }
}

// Be careful when using SSR for cross-request state pollution
// due to creating a Singleton instance here;
// If any client changes this (global) instance, it might be a
// good idea to move this instance creation inside of the
// "export default () => {}" function below (which runs individually
// for each client)
const api = axios.create({ baseURL: `${process.env.host}/` })

export default boot(({ app, router }) => {
  setUnauthorizedHandler(async () => {
    const { useUserStore } = await import('src/stores/modules/userStore')
    useUserStore().clearLocalSession()

    const { resetSessionStores } = await import('src/stores/resetSessionStores')
    resetSessionStores()

    if (router.currentRoute.value.name !== 'login') {
      await router.push({ name: 'login' })
    }
  })

  // for use inside Vue files (Options API) through this.$axios and this.$api
  api.interceptors.request.use(config => {
    config.headers.accept = 'application/json, text/plain, */*'
    const accessToken = getAccessToken()
    if (accessToken) {
      config.headers.authorization = `Bearer ${accessToken}`
    }

    config.sessionEpoch = getSessionEpoch()

    return config
  }, error => {
    return Promise.reject(error)
  })

  api.interceptors.response.use(
    response => {
      const epoch = response.config.sessionEpoch
      if (typeof epoch === 'number' && epoch !== getSessionEpoch()) {
        return Promise.reject(new axios.CanceledError())
      }

      return response
    },
    error => {
      if (axios.isCancel(error)) {
        return Promise.reject(error)
      }

      if (error.response) {
        // Auth errors
        if (error.response.status === 401) {
          handleUnauthorized()
        }
      } else if (error.request) {
        // Errors that did not receive a response from the server (including CORS errors)
        if (error.message.includes('Network Error')) {
          Notify.create({
            type: 'negative',
            message: 'A network error has occurred. Please check your connection or CORS settings.'
          })
        }
      } else {
        // Other errors
        Notify.create({
          type: 'negative',
          message: error.message
        })
      }

      return Promise.reject(error)
    }
  )

  app.config.globalProperties.$axios = axios
  // ^ ^ ^ this will allow you to use this.$axios (for Vue Options API form)
  //       so you won't necessarily have to import axios in each vue file

  app.config.globalProperties.$api = api
  // ^ ^ ^ this will allow you to use this.$api (for Vue Options API form)
  //       so you can easily perform requests against your app's API
})

export { api }
