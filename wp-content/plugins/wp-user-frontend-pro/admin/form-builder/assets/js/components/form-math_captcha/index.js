/**
 * Field template: Math Captcha
 */
Vue.component('form-math_captcha', {
    template: '#tmpl-wpuf-form-math_captcha',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        captcha: () => {
            let operators = [ '+', '-', 'x' ],
                random = Math.floor( Math.random() * operators.length );

            return {
                operandOne: Math.floor( Math.random() * 200 ) + 1,
                operandTwo: Math.floor( Math.random() * 200 ) + 1,
                operator: operators[random]
            }  
        }
    },
});
