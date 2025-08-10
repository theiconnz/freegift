define(function () {
    return function (Component) {
        return Component.extend({
            getItemGiftStatus: function (item) {
                if (!item || !item.options) return false;
                return item.options.some(function (option) {
                    return option.label === 'pharma_gift_item';
                });
            }
        });
    };
});
