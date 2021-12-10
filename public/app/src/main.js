import { v4 as uuidv4 } from 'uuid'
import {createApp} from 'vue'
import App from './App.vue'

const appIds = document.querySelectorAll('[id^="mosa-forms-app"]');

appIds.forEach(appId => {
    const app = createApp(App)

    app.config.globalProperties.form_id = appId.dataset.formId
    app.provide('formId', app.config.globalProperties.form_id)
    app.provide('uuid', uuidv4())

    app.mount(`#${appId.id}`)
});
