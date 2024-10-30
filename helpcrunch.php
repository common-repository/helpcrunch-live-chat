<?php
/**
 * Plugin Name: HelpCrunch Live Chat
 * Description: A modern live chat, email marketing tool, marketing automation solution and simple CRM in one product.
 * Author: HelpCrunch
 * Author URI: https://helpcrunch.com
 * Version: 2.0.6
 */

/**
 * Class HelpCrunchWPSettingsPage
 */
class HelpCrunchWPSettingsPage
{
    /****
     * @var HelpCrunchWPSettings HelpCrunchWPSettings
     */
    private $settings;

    /****
     * @var string
     */
    private $slug;

    /****
     * @var string
     */
    private static $imagesPath;

    /****
     * HelpCrunchWPSettingsPage constructor.
     * @param HelpCrunchWPSettings $settings
     * @param string $slug
     */
    public function __construct(HelpCrunchWPSettings $settings, $slug)
    {
        $this->settings = $settings;
        $this->slug = $slug;

        self::$imagesPath = plugins_url('img/', __FILE__);
    }

  /**
   *
   *
   * Registered hooks info
   */
    public function registerHooks()
    {
        add_action('admin_init', array($this, 'registerSettings'));
        add_action('admin_menu', array($this, 'addSettingsMenu'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'pluginActionLinks'));
    }

    public function registerSettings()
    {
        add_settings_section('integration', __('Setup', 'helpcrunch'),
            array($this, 'integrationSection'), $this->slug);
        add_settings_section('api_code', __('HelpCrunch Code', 'helpcrunch'),
            array($this, 'apiCodeField'), $this->slug);

        register_setting(
            $this->slug,
            $this->settings->getOptionName()
        );
    }

    /**
     * Add our page into the settings tree in the left menu in admin panel
     */
    public function addSettingsMenu()
    {
        add_options_page(
            'HelpCrunch Settings',
            'HelpCrunch',
            'manage_options',
            $this->slug,
            array($this, 'settingsPage')
        );
    }

    /**
     * Shows integration section info
     */
    public function integrationSection()
    {
        ?>
        <stype>
        <ol>
          <li>
              <a href="https://helpcrunch.com/signin.html" target="_blank">Log in</a>
              to your HelpCrunch account in a separate browser tab.
          </li>
          <li>
              Go to <b>Settings → Channels → Website Widgets</b> and choose the widget you'd like to install on your WordPress website.
              <a href="<?php echo self::$imagesPath . 'choose-helpcrunch-widget.png' ?>"
                  target="_blank"
              >
                  <img src="<?php echo self::$imagesPath . 'choose-helpcrunch-widget.png' ?>"
                      style="display: block; width: 100%; max-width: 700px;"
                      alt="Widger Settings"
                  >
              </a>
          </li>
          <li>
              You will be redirected to the <b>Installation</b> page. Select WordPress installation guide and copy the code snippet from the HTML box.
              <a href="<?php echo self::$imagesPath . 'copy-helpcrunch-widget-code.png' ?>"
                  target="_blank"
              >
                  <img src="<?php echo self::$imagesPath . 'copy-helpcrunch-widget-code.png' ?>"
                      style="display: block; width: 100%; max-width: 700px;"
                      alt="Installation page"
                  >
              </a>
          </li>
          <li>Insert the copied HelpCrunch code snippet into the <b>HelpCrunch Code</b> field below.</li>
          <li>Press <b>Save Changes</b> button. Don’t forget to activate HelpCrunch plugin.</li>
        </ol>
        <p>
          If you have any problems with the setup, please check the
          <a href="https://docs.helpcrunch.com/installation/helpcrunch-installation-guide-wordpress"
              target="_blank"
          >installation guide</a>
        </p>
        <p>
          If you have any questions, just <a href="https://helpcrunch.com/signin.html" target="_blank">
            log in to your HelpCrunch account</a>
          and chat with us. We’ll help you with everything 🤗
        </p>
        <?php
    }

    /**
     * Show api Code fields
     */
    public function apiCodeField()
    {
        ?>
            <textarea name="<?php echo esc_attr($this->settings->getOptionName() . '[api_code]'); ?>"
              class="code"
              rows="4"
              style="width: 100%; max-width: 704px;"
            ><?php if ($this->settings->integrated()) {
                $helpcunchApiCode = $this->settings->getApiCode();

                if (is_array($helpcunchApiCode)) {
                    echo esc_attr(json_encode($helpcunchApiCode));
                } else {
                    echo $helpcunchApiCode;
                }
            } ?></textarea>
        <?php
    }

    /**
     * @param array $links
     * @return array
     */
    public function pluginActionLinks($links)
    {
        $links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=' . $this->slug) ) .'">Settings</a>';

        return $links;
    }

    /**
     * Renders the settings page in plugins
     */
    public function settingsPage()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Access Denied');
        }
        ?>
        <div class="wrap">
            <h2><?php _e('HelpCrunch Settings', 'helpcrunch'); ?></h2>
            <form method="post" action="options.php">
                <?php
                    settings_fields($this->slug);
                    do_settings_sections($this->slug);
                    submit_button();
                ?>
            </form>
         </div>
        <?php
    }
}

/**
 * Class HelpCrunchWPSettings
 */
class HelpCrunchWPSettings
{
    /**
     * @var string
     */
    private $optionName;

    /**
     * HelpCrunchWPSettings constructor.
     * @param string $optionName
     */
    public function __construct($optionName)
    {
        $this->optionName = $optionName;
    }

    /**
     * Updates option via default WP way
     */
    public function activate()
    {
        $option = $this->getOption();
        if (empty($option)) {
            update_option($this->optionName, $this->getDefaultOptions());
        }
    }

    /**
     * @param string|null $key
     * @return mixed
     */
    public function getOption($key = null)
    {
        $option = get_option($this->optionName);

        if ($key && isset($option[$key])) {
            return $option[$key];
        }

        return $option;
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return array(
            'api_code' => '',
        );
    }

    /**
     * @return string
     */
    public function getOptionName()
    {
        return $this->optionName;
    }

    /**
     * @return string
     */
    public function getApiCode()
    {
        return $this->getOption('api_code');
    }

    /**
     * @return string|null
     */
    public function getOrganization()
    {
        $apiCode = $this->getApiCode();

        return isset($apiCode['organization']) ? $apiCode['organization'] : null;
    }

    /**
     * @return bool
     */
    public function integrated()
    {
        $apiCode = $this->getApiCode();

        return !empty($apiCode);
    }
}

/**
 * Class HelpCrunchWPWidget
 */
class HelpCrunchWPWidget
{
    /**
     * @var HelpCrunchWPSettings
     */
    private $settings;

    /**
     * HelpCrunchWPWidget constructor.
     * @param HelpCrunchWPSettings $settings
     */
    public function __construct(HelpCrunchWPSettings $settings)
    {
        $this->settings = $settings;
    }

    public function registerHooks()
    {
        if ($this->settings->integrated()) {
            add_action('wp_head', array($this, 'addSDK'));
        }
    }

    /**
     * Generates our code
     */
    public function addSDK()
    {
        $settingApiCode = $this->settings->getApiCode();
        $apiCode = $settingApiCode;
        $isOldVersion = is_array($settingApiCode);

        if (!is_array($settingApiCode)) {
            $apiCode = json_decode($settingApiCode, true);
            $isOldVersion = isset($apiCode['application_secret']);
        }

        if ($isOldVersion) {
            if (!is_array($apiCode)) {
                $apiCode = json_decode($apiCode, true);
            }

            $init = array(
                'applicationId' => $apiCode['application_id'],
                'applicationSecret' => $apiCode['application_secret']
            );

            // var_dump($init);

            ?>
            <script type="text/javascript">
                (function(w,d){
                w.HelpCrunch=function(){w.HelpCrunch.q.push(arguments)};w.HelpCrunch.q=[];
                function r(){var s=document.createElement('script');s.async=1;s.type='text/javascript';s.src='https://embed.helpcrunch.com/sdk.js';(d.body||d.head).appendChild(s);}
                if(w.attachEvent){w.attachEvent('onload',r)}else{w.addEventListener('load',r,false)}
                })(window, document)
            </script>
            <script type="text/javascript">
                HelpCrunch('init', '<?php echo $apiCode['organization'] ?>', <?php echo json_encode($init); ?>);
                HelpCrunch('showChatWidget');
            </script>
            <?php
        } else {
            ?>
            <script type="text/javascript">
                window.helpcrunchSettings = <?php echo $settingApiCode ?>;
            </script>

            <script type="text/javascript">
              (function(w,d){var hS=w.helpcrunchSettings;if(!hS||!hS.organization){return;}var widgetSrc='https://embed.helpcrunch.com/sdk.js';w.HelpCrunch=function(){w.HelpCrunch.q.push(arguments)};w.HelpCrunch.q=[];function r(){if (d.querySelector('script[src="' + widgetSrc + '"')) { return; }var s=d.createElement('script');s.async=1;s.type='text/javascript';s.src=widgetSrc;(d.body||d.head).appendChild(s);}if(d.readyState === 'complete'||hS.loadImmediately){r();} else if(w.attachEvent){w.attachEvent('onload',r)}else{w.addEventListener('load',r,false)}})(window, document);
            </script>
            <?php
        }
    }
}

/**
 * Class HelpCrunchWP
 */
class HelpCrunchWP
{
    /**
     * @var
     */
    private static $instance;

    /**
     * @var
     */
    private $settings;
    /**
     * @var HelpCrunchWPWidget
     */
    private $widget;

    /**
     * HelpCrunchWP constructor.
     */
    public function __construct()
    {
        $settings = $this->getSettings();
        if (is_admin()){
            $settingsPage = new HelpCrunchWPSettingsPage($settings, 'helpcrunch');
            $settingsPage->registerHooks();
        }
        $this->widget = new HelpCrunchWPWidget($settings);
        $this->widget->registerHooks();
    }

    /**
     * @return HelpCrunchWPSettings
     */
    public function getSettings()
    {
        if (!$this->settings) {
            $this->settings = new HelpCrunchWPSettings('helpcrunch');
        }

        return $this->settings;
    }

    /**
     * @return HelpCrunchWP
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function activate()
    {
        self::getInstance()->getSettings()->activate();
    }
}

register_activation_hook(__FILE__, array('HelpCrunchWP', 'activate'));
add_action('plugins_loaded', 'HelpCrunchWP::getInstance');
