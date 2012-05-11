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
var OrderSendSync = Class.create();
OrderSendSync.prototype = {
    initialize: function() {
        this.varienForm =  new varienForm('config_edit_form');
        this.redirectURL = null;
        this.popupWindow = null;
        /**
         * Initialized channel_order_ajax_responce in
         * Xcom_Ebay_Block_Adminhtml_System_Form_Renderer_Config_OrderSyncStartManual
         */
        this.initAuthorizationMessageUrl = channel_order_ajax_responce;
        this.advices = [''];

        $('order_sync').observe('click', this.sendOrderSyncData.bindAsEventListener(this));
    },

    sendOrderSyncData: function() {
        if (!this.validateForm()) {
            return;
        }
        $('system-order-sync-not-valid').hide();
        this.disableButton();
        new Ajax.Request(this.initAuthorizationMessageUrl, {
            method: 'post',
            parameters: {
                account: $('xcom_channel_ebay_order_sync_ebay_account').value,
                start_date: $('xcom_channel_ebay_order_sync_start_date').value,
                start_time_hour: $$('[name="groups[ebay][fields][order_sync_start_time][value][]"]')[0].value,
                start_time_minute: $$('[name="groups[ebay][fields][order_sync_start_time][value][]"]')[1].value,
                start_time_seconds: $$('[name="groups[ebay][fields][order_sync_start_time][value][]"]')[2].value,
                end_date: $('xcom_channel_ebay_order_sync_end_date').value,
                end_time_hour: $$('[name="groups[ebay][fields][order_sync_end_time][value][]"]')[0].value,
                end_time_minute: $$('[name="groups[ebay][fields][order_sync_end_time][value][]"]')[1].value,
                end_time_seconds: $$('[name="groups[ebay][fields][order_sync_end_time][value][]"]')[2].value
            },
            onSuccess: function(transport) {
                try {
                    var response = transport.responseText.evalJSON();
                    if (response.error) {
                        this.enableButton();
                        $('system-order-sync-not-valid').show();
                        $('system-order-sync-not-valid-message').replace(
                            '<div id="system-order-sync-not-valid-message"><ul class="messages">' +
                            '<li class="error-msg"><ul><li>' +
                            '<span>' + response.message +
                            '</span></li></ul></li></ul></div>');
                    } else {
                        $('system-order-sync-not-valid').show();
                        $('system-order-sync-not-valid-message').replace(
                            '<div id="system-order-sync-not-valid-message"><ul class="messages">' +
                            '<li class="success-msg"><ul><li>' +
                            '<span>' + response.message +
                            '</span></li></ul></li></ul></div>');
                        this.enableButton();
                    }
                }
                catch (e) {
                    this.enableButton();
                }
            }.bind(this)
        });
    },

    disableButton: function() {
        $('order_sync').disabled = 1;
        $('order_sync').removeClassName('form-button');
        $('order_sync').addClassName('disabled');
    },

    enableButton: function() {
        $('order_sync').disabled = 0;
        $('order_sync').removeClassName('disabled');
        $('order_sync').addClassName('form-button');
    },
    validateElement: function(elem, msg, state) {
        var result = 1;
        var advice = '<div class="validation-advice" id="advice-' + elem.name + '-' + elem.identify() +'">'
            + msg + '</div>';
        if (state) {
            result = 0;
            this.showOrHideAdvice(elem, advice);
        } else {
            this.hideAdvice(elem, this.getAdvice(elem.name, elem));
        }
        return result;
    },
    validateForm: function() {
        var validated = 1;
        var elem = $('xcom_channel_ebay_order_sync_ebay_account');
        if (!this.validateElement(elem, 'Please select an option.', this.isFieldFailed(elem, 'select'))) {
            validated = 0;
        }

        elem = $('xcom_channel_ebay_order_sync_start_date_trig');
        if (!this.validateElement(elem, 'Please enter a valid date.',
            this.isFieldFailed($('xcom_channel_ebay_order_sync_start_date'), 'date'))) {
            validated = 0;
        }

        elem = $('xcom_channel_ebay_order_sync_end_date_trig');
        if (!this.validateElement(elem, 'Please enter a valid date.',
            this.isFieldFailed($('xcom_channel_ebay_order_sync_end_date'), 'date'))) {
            validated = 0;
        }

        if (!validated) {
            $('system-order-sync-not-valid').show();
            return;
        }
        return true;
    },
    isFieldFailed: function(elem, type) {
        if ('select' == type) {
            return ((elem.value == "none") || (elem.value == null) || (elem.value.length == 0));
        }
        if ('date' == type) {
            var test = new Date(elem.value);
            return Validation.get('IsEmpty').test(elem.value) || isNaN(test);
        }
    },
    showOrHideAdvice: function(elem, after) {
        if (!this.getAdvice(elem.name, elem)) {
            Element.insert(elem, {after: after});
        } else {
            this.getAdvice(elem.name, elem).setStyle({
                display: 'block',
                opacity: 1
            });
        }
    },
    submit: function() {
        $('system-order-sync-not-valid').hide();
        if (!this.validateForm()) {
            return;
        }
        this.varienForm.submit();
    },
    getAdvice : function(name, elm) {
        return $('advice-' + name + '-' + Validation.getElmID(elm)) || $('advice-' + Validation.getElmID(elm));
    },
    hideAdvice : function(elm, advice){
        if (advice != null) {
            new Effect.Fade(advice, {duration : 1, afterFinishInternal : function() {advice.hide();}});
        }
    }
};
var orderSendSync = new OrderSendSync();
