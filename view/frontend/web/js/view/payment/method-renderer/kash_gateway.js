/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/checkout-data',
        'mage/url',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/view/summary/grand-total',
        'jquery',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/customer-data',
        'mage/translate'
    ],
    function (Component, checkoutData, urlBuilder, Quote, GrandTotal, $, ko, customer, customerData) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Kash_Gateway/payment/kash_gateway',
                code: 'kash_gateway',
                title: 'Kash Gateway',
                accountSetupNonce: null,
                accountSetupName: null,
                kashCustomerId: null,
                transactionId: null,
                completedAccountSetup: ko.observable(false),
                // customerData only contains data when customer is logged in
                email: customer.isLoggedIn() ? customerData.email : checkoutData.getValidatedEmailValue(),
                maxAttempts: 3
            },

            createCustomer: function() {
                this.showLoader();
                var url = urlBuilder.build("kash/offsite/createcustomer");
                var data = {};

                data["account_setup_nonce"] = this.accountSetupNonce;
                data["email"] = this.email;
                $.ajax({
                    url: url,
                    type: "POST",
                    data: data,
                    async: true,
                    success: function(result){
                        this.isPlaceOrderActionAllowed(true);
                        this.kashCustomerId = result.id;
                        this.completedAccountSetup(true);
                        document.getElementById("kash-account-label").innerHTML = "<b> Selected Account: </b>" + this.accountSetupName;
                        this.hideLoader();
                        this.clearError();
                    }.bind(this),
                    error: function(err){
                        this.setError($.mage.__("Error: Could not create customer"));
                        this.hideLoader();
                        this.createIFrame();
                        return;
                    }.bind(this)
                });
            },

            createIFrame: function () {
                var options = {
                    hideTitleBar: true,
                    hideNavigationBar: true
                };

                Kash.startAccountSetupUI(
                    document.getElementById("kash-gateway-frame"),
                    this.accountSetupToken,
                    options,
                    function(err, result) {
                        if (err) {
                            this.setError($.mage.__("Error: Could not complete bank login. Please try again."));
                            this.createIFrame();
                            return;
                        }

                        if (result) {
                            this.clearError();
                            this.accountSetupName = result.accountName;
                            this.accountSetupNonce = result.accountSetupNonce;

                            this.createCustomer();
                        } else {
                            // customer cancelled with back button
                            this.createIFrame();
                            return;
                        }
                    }.bind(this)
                );
            },

            initializeToken: function(count) {
                if (count < this.maxAttempts) {
                    var url = urlBuilder.build('kash/offsite/generatetoken');
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {"email": this.email},
                        async: true,
                        success: function (result) {
                            this.accountSetupToken = result.account_setup_token;
                            this.hideLoader();
                            this.createIFrame();
                        }.bind(this),
                        error: function () {
                            this.initializeToken(count + 1);
                        }.bind(this)
                    });
                } else {
                    this.hideLoader();
                    this.setError($.mage.__("Error: Could not initialize account setup"));
                }
            },

            initialize: function() {
                this._super();
                this.showLoader();
                this.initializeToken(0);
            },

            getCode: function() {
                return this.code;
            },

            getTitle: function() {
                return this.title;
            },

            isActive: function() {
                return true;
            },

            showLoader: function() {
                $('.kash-spinner').show();
            },

            hideLoader: function() {
                $('.kash-spinner').hide();
            },

            setError: function(text) {
                document.getElementById("kash-error").innerHTML = text;
            },
            clearError: function() {
                this.setError('');
            }

        });
    }
);
