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

        salesChannelDomains: function () {
            return this.previewSalesChannel.domains.map((domain) => {
                return {
                    value: domain.url,
                    label: `${domain.language.name} (${domain.url})`
                };
            });
        },

        previewEnabled: function () {
            return this.previewDomain !== null &&
                this.demoEntityId !== null &&
                this.demoEntity !== null &&
                this.page !== null;
        }
    },

    methods: {
        previewPage: function () {
            const entity = {
                entity: this.demoEntity,
                id: this.demoEntityId
            };

            const previewLink = this.buildPreviewLink(this.previewDomain, this.page, entity);

            const win = window.open(previewLink, '_blank');
            win.focus();
        },

        buildPreviewLink: function (domain, page, entity) {
            let previewLink = `${domain}`;

            if (page.type === 'product_list' && entity.entity === 'category') {
                previewLink += `navigation/${entity.id}`;
            }

            if (page.id) {
                previewLink += `/__preview/${page.id}`;
            }

            return previewLink;
        },

        onSalesChannelChanged: function (salesChannelId) {
            this.previewSalesChannelId = salesChannelId;

            const criteria = new Criteria({limit: 1});
            criteria.ids = [salesChannelId];
            criteria.addAssociation('domains');
            criteria.addAssociation('domains.language');

            this.salesChannelRepository.search(criteria, Context.api).then((salesChannelCollection) => {
                this.previewSalesChannel = salesChannelCollection[0];
            });
        }
    }
});
