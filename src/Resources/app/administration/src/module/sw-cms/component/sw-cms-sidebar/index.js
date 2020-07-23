import template from './sw-cms-sidebar.html.twig';
const { Criteria } = Shopware.Data;
const { Context } = Shopware;

Shopware.Component.override('sw-cms-sidebar', {
    template,

    inject: [
        'repositoryFactory'
    ],

    data: function () {
        return {
            previewSalesChannelId: null,
            previewSalesChannel: {
                domains: []
            },
            previewDomain: null
        }
    },

    computed: {
        salesChannelRepository: function () {
            return this.repositoryFactory.create('sales_channel')
        },

        salesChannelTitle: function () {
            return 'In which Sales Channel?';
        },

        salesChannelDomains: function () {
            return this.previewSalesChannel.domains.map((domain) => {
                return {
                    value: domain.url,
                    label: domain.url
                };
            });
        }
    },

    methods: {
        previewPage: function () {
            var win = window.open(this.previewDomain, '_blank');
            win.focus();
        },

        onSalesChannelChanged: function (salesChannelId) {
            this.previewSalesChannelId = salesChannelId;

            const criteria = new Criteria({limit: 1});
            criteria.ids = [salesChannelId];
            criteria.addAssociation('domains');

            this.salesChannelRepository.search(criteria, Context.api).then((salesChannelCollection) => {
                this.previewSalesChannel = salesChannelCollection[0];
            });
        }
    }
});
