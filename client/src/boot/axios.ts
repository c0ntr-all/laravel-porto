import { boot } from 'quasar/wrappers'
import axios, { AxiosInstance } from 'axios'
import { Notify } from 'quasar'

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $axios: AxiosInstance;
    $api: AxiosInstance;
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
  // for use inside Vue files (Options API) through this.$axios and this.$api
  api.interceptors.request.use(config => {
    config.headers.accept = 'application/json, text/plain, */*'
    if (localStorage.access_token) {
      config.headers.authorization = `Bearer ${localStorage.access_token}`
    }

    return config
  }, error => {
    return Promise.reject(error)
  })

  api.interceptors.response.use(
    response => response,
    error => {
      if (error.response) {
        // Auth errors
        if (error.response.status === 401) {
          localStorage.removeItem('access_token')
          router.push('/login')
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
