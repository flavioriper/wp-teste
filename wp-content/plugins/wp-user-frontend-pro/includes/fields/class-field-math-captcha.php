<?php
/**
 * Math Captcha Field Class
 *
 * @since 3.4.1
 */
class WPUF_Form_Field_Math_Captcha extends WPUF_Field_Contract {

    public function __construct() {
        $this->name = __( 'Math Captcha', 'wpuf-pro' );
        $this->input_type = 'math_captcha';
        $this->icon = 'hashtag';
    }

    /**
     * Render the Math Captcha field
     *
     * @param  array  $field_settings
     *
     * @param  integer  $form_id
     *
     * @param  string  $type
     *
     * @param  integer  $post_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null ) {
        ?>
        <li <?php $this->print_list_attributes( $field_settings ); ?>>
            <?php $this->print_label( $field_settings, $form_id ); ?>

            <div class="wpuf-fields">
                <div class="wpuf-math-captcha">
                    <div class="captcha">
                        <div class="refresh">
                            <svg id="captchaRefresher" fill="#555555" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" baseProfile="tiny" x="0px" y="0px" width="30px" height="30px" viewBox="0 0 480 480" xml:space="preserve" style="vertical-align: bottom;">
                                <g>
                                    <path d="M383.434,172.242c-25.336-58.241-81.998-95.648-145.861-95.648c-65.309,0-125,40.928-148.514,101.827l49.5,19.117   c15.672-40.617,55.469-67.894,99.014-67.894c42.02,0,79.197,24.386,96.408,62.332l-36.117,14.428l92.352,53.279l27.01-100.933   L383.434,172.242z"></path>
                                    <path d="M237.573,342.101c-41.639,0-79.615-25.115-96.592-62.819l35.604-13.763l-91.387-52.119l-27.975,98.249l34.08-13.172   c24.852,58.018,82.859,96.671,146.27,96.671c65.551,0,123.598-39.336,147.871-100.196l-49.268-19.652   C319.981,315.877,281.288,342.101,237.573,342.101z"></path>
                                </g>
                            </svg>
                        </div>
                        <div class="captcha-number-area">
                            <p class="captcha-number">
                                <span id="operand_one"></span>
                                <span id="operator"></span>
                                <span id="operand_two"></span>
                            </p>
                        </div>
                        <div class="captcha-equal">=</div>
                        <div class="wpuf-captcha-input-wrapper">
                            <input
                                type="text"
                                class="textfield wpuf-captcha-input <?php echo 'wpuf_' . $field_settings['name'] . '_' . $form_id; ?>"
                                id="<?php echo $field_settings['name'] . '_' . $form_id; ?>"
                                data-required="<?php echo $field_settings['required']; ?>"
                                placeholder="<?php echo esc_attr( $field_settings['placeholder'] ); ?>"
                                value=""
                                size="<?php echo esc_attr( $field_settings['size'] ); ?>"
                            >
                            <span class="wpuf-captcha-error"></span>
                        </div>
                    </div>
                </div>

                <span class="wpuf-wordlimit-message wpuf-help"></span>
                <?php $this->help_text( $field_settings ); ?>
            </div>

            <script type="text/javascript">
                (function($) {
                    var MathCapta = {
                        /**
                         * Init captcha
                         *
                         * @return void
                         */
                        init: function() {
                            var id = '#' + "<?php echo $field_settings['name'] . '_' . $form_id; ?>";
                            $('.wpuf-math-captcha #captchaRefresher').on( 'click', MathCapta.refreshCaptcha );
                            $(id).parents('form').on( 'submit', MathCapta.validateCaptcha );

                            $('.wpuf-captcha-input').on( 'keyup', MathCapta.removeErrorMessage );

                            MathCapta.setCaptcha();
                        },

                        /**
                         * Get captcha field value
                         *
                         * @return object
                         */
                        getCaptcha: function() {
                            let operators = [ '+', '-', 'x' ],
                            random = Math.floor( Math.random() * operators.length );

                            return {
                                operandOne: Math.floor( Math.random() * 5 ) + 5,
                                operandTwo: Math.floor( Math.random() * 5 ) + 1,
                                operator: operators[random]
                            }
                        },

                        /**
                         * Set captcha field value
                         *
                         * @return void
                         */
                        setCaptcha: function() {
                            let captcha = MathCapta.getCaptcha(),
                                field = MathCapta.captchaField();

                            field.operandOne.text( captcha.operandOne );
                            field.operandTwo.text( captcha.operandTwo );
                            field.operator.text( captcha.operator );
                        },

                        /**
                         * Refresh captcha field and set new value
                         *
                         * @return void
                         */
                        refreshCaptcha: function() {
                            MathCapta.setCaptcha();
                        },

                        /**
                         * Get captcha value container
                         *
                         * @return object
                         */
                        captchaField: function() {
                            return {
                                operandOne: $('.wpuf-math-captcha #operand_one'),
                                operandTwo: $('.wpuf-math-captcha #operand_two'),
                                operator:   $('.wpuf-math-captcha #operator'),
                            }
                        },

                        /**
                         * Validate captcha equation
                         *
                         * @return bool
                         */
                        validateCaptcha: function(e) {
                            e.preventDefault();

                            let captchaInput = $('.wpuf-captcha-input');

                            if (!$(captchaInput).is(':visible')) {
                                return;
                            }

                            let required = captchaInput.data('required'),
                                value = captchaInput.val(),
                                errorMessageContainer = $('.wpuf-captcha-error'),
                                captchaEquationValue = MathCapta.getEquationValue(),
                                errorMessage = [];

                                if ( 'yes' === required && captchaInput.val() === '' ) {
                                    errorMessage.push( 'Please fill up captcha.' );
                                }

                                if ( value && value != captchaEquationValue ) {
                                    errorMessage.push( 'Invalid captcha.' );
                                }

                                if ( errorMessage.length ) {
                                    e.stopImmediatePropagation();
                                    errorMessageContainer.text( errorMessage[0] );
                                    errorMessage = [];
                                    return false;
                                }

                                MathCapta.refreshCaptcha();
                                captchaInput.val('');
                        },

                        /**
                         * Get captcha equation value
                         *
                         * @return integer
                         */
                        getEquationValue: function() {
                            let captchaField = MathCapta.captchaField(),
                                valueOne     = captchaField.operandOne.text(),
                                valueTwo     = captchaField.operandTwo.text(),
                                operator     = captchaField.operator.text();

                                if ( operator === 'x' ) {
                                    operator = '*';
                                }

                                equation = valueOne + operator + valueTwo;

                            return eval( equation );
                        },

                        /**
                         * Remove captcha error message
                         *
                         * @return void
                         */
                        removeErrorMessage: function() {
                            $('.wpuf-captcha-error').empty();
                        }
                    }

                    MathCapta.init();
                })(jQuery);
            </script>
        </li>

        <?php
    }

    /**
    * Get field options setting
    *
    * @return array
    **/
    public function get_options_settings() {
        $default_options = $this->get_default_option_settings( false, array( 'dynamic' ) );

        return $default_options;
    }

    /**
     * Get the field props
     *
     * @return array
     * @author
     **/
    public function get_field_props() {
        $defaults = $this->default_attributes();

        $props = array(
            'input_type'        => 'numeric_text',
            'input_type'    => 'text',
            'required'      => 'yes',
            'name'          => 'math_captcha',
            'is_meta'       => 'no',
            'help'          => '',
            'css'           => '',
            'placeholder'   => '',
            'default'       => '',
            'size'          => 40,
            'id'            => 0,
            'is_new'        => true,
        );

        return array_merge( $defaults, $props );
    }
}
