/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Xcom
 * @package     Xcom_Mmp
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

var xcomGridMassaction = new Class.create(varienGridMassaction, {
    confirmMessage : '',
    apply: function() {
        if(varienStringArray.count(this.checkedString) == 0) {
                alert(this.errorText);
                return;
            }

        var item = this.getSelectedItem();
        if(!item) {
            this.validator.validate();
            return;
        }
        this.currentItem = item;
        var fieldName = (item.field ? item.field : this.formFieldName);
        var fieldsHtml = '';

        this.formHiddens.update('');
        new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name: fieldName, value: this.checkedString}));
        new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name: 'massaction_prepare_key', value: fieldName}));

        this.validateItem();

        if(this.currentItem.confirm && !window.confirm(this.currentItem.confirm)) {
            return;
        }

        if(!this.validator.validate()) {
            return;
        }

        if(this.useAjax && item.url) {
            new Ajax.Request(item.url, {
                'method': 'post',
                'parameters': this.form.serialize(true),
                'onComplete': this.onMassactionComplete.bind(this)
            });
        } else if(item.url) {
            this.form.action = item.url;
            this.form.submit();
        }
    },

    validateItem : function()
    {
        if (!this.currentItem.validate_url) {
            return;
        }
        new Ajax.Request(this.currentItem.validate_url, {
            'asynchronous' : false,
            'method': 'post',
            'parameters': this.form.serialize(true),
            'onComplete': this.onMassactionValidateComplete.bind(this)
        });
    },

    onMassactionValidateComplete : function(transport)
    {
        toggleSelectsUnderBlock($('loading-mask'), true);
        Element.hide('loading-mask');

        if (!transport.responseText) {
            if (this.confirmMessage) {
                this.currentItem.confirm = this.confirmMessage;
            }
            return;
        }
        var response = transport.responseText.evalJSON();
        if (!response.message) {
            if (this.confirmMessage) {
                this.currentItem.confirm = this.confirmMessage;
            }
            return;
        }
        this.updateConfirmMessage();
        this.currentItem.confirm = response.message + '\n' + this.confirmMessage;
    },

    updateConfirmMessage : function()
    {
        if (this.currentItem.confirm && !this.confirmMessage) {
            this.confirmMessage = this.currentItem.confirm;
        }
    }
});

var xcomEbayGridMassaction = new Class.create(varienGridMassaction, {
    apply: function() {
        if(varienStringArray.count(this.checkedString) == 0) {
                alert(this.errorText);
                return;
            }

        var item = this.getSelectedItem();
        if(!item) {
            this.validator.validate();
            return;
        }
        this.currentItem = item;
        var fieldName = (item.field ? item.field : this.formFieldName);
        var fieldsHtml = '';

        this.formHiddens.update('');
        new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name: fieldName, value: this.checkedString}));
        new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name: 'massaction_prepare_key', value: fieldName}));

        if(!this.validator.validate()) {
            return;
        }

        if(this.currentItem.confirm && !window.confirm(this.currentItem.confirm)) {
            return;
        }

        if(this.useAjax && item.url) {
            new Ajax.Request(item.url, {
                'method': 'post',
                'parameters': this.form.serialize(true),
                'onComplete': this.onMassactionComplete.bind(this)
            });
        } else if(item.url) {
            this.form.action = item.url;
            this.form.submit();
        }
    }
});
