    <footer class="footer">
        <div class="container">
            <?php
            wp_nav_menu( array(
                'theme_location' => 'footer-menu',
                'container'      => false,
                'menu_class'     => 'footer-links',
                'fallback_cb'    => '__return_false',
            ) );
            ?>
            <?php if ( ! has_nav_menu( 'footer-menu' ) ) : ?>
            <ul class="footer-links">
                <li><a href="#">Главная</a></li>
                <li><a href="#">Как купить?</a></li>
                <li><a href="#">Доставка</a></li>
                <li><a href="#">Оплата</a></li>
                <li><a href="#">Контакты</a></li>
            </ul>
            <?php endif; ?>

            <div class="footer-bottom">
                <div class="copyright">
                    &copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'Все права защищены.', 'gamestore' ); ?>
                </div>
                <div class="social-links">
                    <?php
                    if ( function_exists( 'gamestore_get_social_links' ) ) {
                        $social_links = gamestore_get_social_links();
                        foreach ( $social_links as $item ) {
                            if ( empty( $item['enabled'] ) || '1' !== (string) $item['enabled'] ) {
                                continue;
                            }
                            if ( empty( $item['url'] ) ) {
                                continue;
                            }
                            ?>
                            <a href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $item['label'] ); ?>">
                                <i class="<?php echo esc_attr( $item['icon'] ); ?>" aria-hidden="true"></i>
                            </a>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div id="login-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div class="login-form-container">
                <h2>Вход в аккаунт</h2>
                <?php if ( class_exists( 'WooCommerce' ) ) : ?>
                    <form class="woocommerce-form woocommerce-form-login login" method="post">
                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <label for="username">Имя пользователя или почта <span class="required">*</span></label>
                            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="" />
                        </p>
                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <label for="password">Пароль <span class="required">*</span></label>
                            <input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" />
                        </p>
                        <p class="form-row">
                            <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
                                <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span>Запомнить меня</span>
                            </label>
                            <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
                            <button type="submit" class="woocommerce-button button woocommerce-form-login__submit" name="login" value="Войти">Войти</button>
                        </p>
                        <p class="woocommerce-LostPassword lost_password">
                            <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">Забыли пароль?</a>
                        </p>
                        <p class="register-link">
                            Нет аккаунта? <a href="<?php echo esc_url( get_permalink( get_option('woocommerce_myaccount_page_id') ) ); ?>#register">Зарегистрироваться</a>
                        </p>
                    </form>
                <?php else : ?>
                    <?php wp_login_form(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
