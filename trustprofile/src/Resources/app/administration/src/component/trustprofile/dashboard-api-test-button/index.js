const { Component, Mixin } = Shopware;
import template from './dashboard-api-test-button.html.twig';

Component.register('dashboard-api-test-button', {
    template,

    props: ['label'],
    inject: ['dashboardApiTest'],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
        };
    },

    computed: {
        pluginConfig() {
            let $parent = this.$parent;

            while ($parent.actualConfigData === undefined) {
                $parent = $parent.$parent;
            }

            return $parent.actualConfigData.null;
        }
    },

    methods: {
        saveFinish() {
            this.isSaveSuccessful = false;
        },

        check() {
            this.isLoading = true;
            this.dashboardApiTest.check(this.pluginConfig).then((res) => {
                if (res.success) {
                    this.isSaveSuccessful = true;
                    this.createNotificationSuccess({
                        title: this.$tc('dashboard-api-test-button.title'),
                        message: this.$tc('dashboard-api-test-button.success')
                    });
                } else {
                    this.createNotificationError({
                        title: this.$tc('dashboard-api-test-button.title'),
                        message: this.$tc('dashboard-api-test-button.error')
                    });
                }

                this.isLoading = false;
            });
        }
    }
})