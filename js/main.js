$(function () {
    /**
     * TODO
     * 3. серверная валидация
     * 4. отправка email
     */
    /**
     * Cache DOM
     */
    var block = $('.contactform');

    if (typeof block === "undefined") return false;
    /**
     * Initialize mask plugin
     */
    block.find('.phone').mask('+7 (000) 000-00-00');

    /**
     * Allow cyrillic chars and spaces
     */
    block.find('.cyrillic').keyup(function () {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
                // Allow: Ctrl+A
            (e.keyCode == 65 && e.ctrlKey === true) ||
                // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return true;
        }
        this.value = this.value.replace(/[^а-я ]/i, "");
        return true;
    });

    /**
     * Send data to a server
     * @returns {boolean}
     */
    var submitForm = function () {
        var ready = true,
            self = $(this);

        /**
         * Validate required
         */
        block.find('.required').each(function (i, obj) {
            var field = $(obj);
            if (field.val().length < 1) {
                field.addClass('error');
                ready = false;
            }
        });

        /**
         * Validate Email
         */
        var email = block.find('.email');
        if (validateEmail(email.val()) !== true) {
            email.addClass('error');
            ready = false;
        }

        /**
         * Send data to a server
         */
        if (ready === true) {
            var data = self.serialize();
            /**
             * TODO send data to a server
             */
            console.log(data);
        }
        /**
         * Prevent default behavior
         */
        return false;
    };

    /**
     * Validate an email
     * @param val
     * @returns {boolean}
     */
    var validateEmail = function (val) {
        var pattern = /^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i;
        return pattern.test(val);
    };

    /**
     * Remove error class on change
     * @returns {boolean}
     */
    block.find('.required').on('change', function () {
        $(this).removeClass('error');
        return true;
    });

    /**
     * Validate e-mail on focusout
     */
    block.find('.email').on('focusout', function () {
        var email = $(this);
        if (validateEmail(email.val()) !== true) {
            email.addClass('error');
        }
        return true;
    });

    /**
     * Form submit event
     */
    block.find('form').on('submit', submitForm);

    /**
     * Cities typeahead
     */
    var citiesAdapter = new Bloodhound({
        datumTokenizer: function (datum) {
            return Bloodhound.tokenizers.whitespace(datum.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: 'api/city/%QUERY',
            filter: function (cities) {
                return $.map(cities, function (city) {
                    return {
                        value: city
                    };
                });
            }
        }
    });
    citiesAdapter.initialize();
    block.find('.cities').typeahead(null, {
        name: 'cities',
        hint: false,
        displayKey: 'value',
        source: citiesAdapter.ttAdapter()
    }).on('typeahead:selected', function () {
        $(this).removeClass('error');
        return true;
    });
});
