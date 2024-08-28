<?php

/**
 * Load the branding styles and scripts
 *
 * @since 1.0.0
 */

namespace Tempel\Settings;

class Branding
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('get_header', array($this, 'remove_admin_bar_callback_action'));
        add_action('admin_bar_menu', array($this, 'add_logo_to_admin_bar'), 1);
        add_action('admin_bar_menu', array($this, 'remove_wp_logo_from_admin_bar'), 999);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_admin_bar_theme'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_theme'));
        add_action('login_enqueue_scripts', array($this, 'enqueue_login_theme'));
        add_action('login_enqueue_scripts', array($this, 'dequeue_login_styles'));
        add_action('admin_footer_text', array($this, 'admin_footer_text'));
        
        
        // add_action('login_footer', array($this, 'add_script_to_login'));
        add_action('login_header', [$this, 'add_header_to_login']);
        add_filter('gettext', [$this, 'change_lost_password_text']);
        add_filter('gettext', [$this, 'change_new_password_text']);
//        add_action('login_enqueue_scripts', [$this, 'add_background_image_to_login']);
    }
    
    function change_lost_password_text($text)
    {
        if (in_array($GLOBALS['pagenow'], array('wp-login.php'))) {
            if ($text == 'Je wachtwoord vergeten?') {
                $text = 'Vergeten?';
            }
            return $text;
        }
        
        return $text;
    }
    
    function change_new_password_text($text)
    {
        if (in_array($GLOBALS['pagenow'], array('wp-login.php'))) {
            if ($text == 'Nieuw wachtwoord aanmaken') {
                $text = 'Verzend';
            }
            return $text;
        }
        
        return $text;
    }
    
    function add_background_image_to_login()
    {
        $option = get_option('tempel-login-page-settings-data');
        $login_bg_image = $option['login-bg-image'] ?? false;
        if ($login_bg_image) {
            ?>
            <style>
                body.login {
                    background-image: url('<?= $login_bg_image; ?>') !important;
                    background-size: cover;
                    background-position: center;
                }
            </style>
            <?php
        }
    }
    
    /**
     * Add the Studio Tempel logo to the login screen
     *
     * @since 1.0.0
     */
    function add_header_to_login()
    {
        ?>
        <div class="header">
            <div class="logo">
                <svg width="131px" height="20px" viewBox="0 0 131 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <g id="Login" transform="translate(-205, -278)" fill="#000000">
                            <path d="M208.298895,288.645333 C208.371718,289.584 208.681738,290.266667 209.227912,290.693333 C209.774087,291.12 210.578264,291.333333 211.641484,291.333333 C212.151247,291.333333 212.580905,291.279492 212.930457,291.172825 C213.280008,291.066159 213.56506,290.923937 213.782489,290.746159 C214.000959,290.569397 214.158049,290.359111 214.252719,290.116317 C214.346349,289.875556 214.394205,289.612444 214.394205,289.328 C214.394205,288.929778 214.241276,288.555937 213.935418,288.207492 C213.62956,287.860063 213.068821,287.607111 212.2532,287.449651 L210.22247,287.088 C209.420374,286.945778 208.713988,286.760889 208.103313,286.533333 C207.491597,286.305778 206.976632,286.010159 206.56258,285.647492 C206.147487,285.284825 205.830186,284.840889 205.611716,284.313651 C205.393246,283.788444 205.285051,283.162667 205.285051,282.437333 C205.285051,281.712 205.432778,281.072 205.731354,280.517333 C206.029929,279.961651 206.446063,279.500444 206.976632,279.130667 C207.508242,278.760889 208.147007,278.479492 208.888764,278.287492 C209.631561,278.095492 210.447182,278 211.335626,278 C212.47167,278 213.418373,278.126984 214.175735,278.382984 C214.933097,278.638984 215.544813,278.97727 216.010882,279.396825 C216.476951,279.817397 216.822341,280.304 217.049134,280.858667 C217.274886,281.413333 217.423654,281.988317 217.496477,282.585651 L214.416052,282.970667 C214.25584,282.131556 213.946862,281.527111 213.487035,281.157333 C213.029288,280.787556 212.333306,280.602667 211.401167,280.602667 C210.84771,280.602667 210.393085,280.648381 210.036251,280.740825 C209.678376,280.834286 209.391245,280.954159 209.172775,281.103492 C208.954305,281.252825 208.801376,281.427556 208.713988,281.626667 C208.6266,281.825778 208.582906,282.030984 208.582906,282.244317 C208.582906,282.814222 208.747279,283.240889 209.074983,283.525333 C209.402688,283.809778 209.960307,284.030222 210.746798,284.185651 L212.886763,284.592 C214.576263,284.919111 215.815299,285.449397 216.60075,286.180825 C217.387242,286.91327 217.780488,287.969778 217.780488,289.348317 C217.780488,290.017778 217.652527,290.632381 217.398686,291.194159 C217.143804,291.756952 216.765123,292.246603 216.261602,292.666159 C215.760162,293.085714 215.12972,293.412825 214.372358,293.647492 C213.614995,293.882159 212.726551,294 211.707025,294 C209.580585,294 207.953505,293.555048 206.824744,292.666159 C205.695983,291.77727 205.087388,290.437333 205,288.645333 L208.298895,288.645333 Z M220.398713,284.922206 L218.579268,284.922206 L218.579268,282.58301 L220.398713,282.58301 L220.398713,279.6 L223.505081,279.6 L223.505081,282.58301 L226.167683,282.58301 L226.167683,284.922206 L223.505081,284.922206 L223.505081,290.094188 C223.505081,290.680775 223.631872,291.077283 223.88334,291.285757 C224.133751,291.493208 224.460237,291.596423 224.859627,291.596423 C225.065662,291.596423 225.276979,291.586204 225.491467,291.563722 C225.704897,291.543283 225.916215,291.503428 226.123306,291.4462 L226.567073,293.656632 C226.139155,293.785395 225.720746,293.874303 225.31396,293.925399 C224.907174,293.974452 224.518349,294 224.1496,294 C222.876411,294 221.933935,293.706706 221.320057,293.119097 C220.70618,292.533532 220.398713,291.546349 220.398713,290.158569 L220.398713,284.922206 Z M227.365854,282 L230.471166,282 L230.471166,289.271375 C230.471166,290.104089 230.619088,290.664896 230.914933,290.955921 C231.211834,291.244822 231.617564,291.390335 232.136348,291.390335 C232.535738,291.390335 232.864337,291.330855 233.123201,291.211896 C233.382065,291.092937 233.630364,290.921933 233.865983,290.697823 C234.103715,290.490706 234.291788,290.242167 234.432314,289.951142 C234.57284,289.662241 234.643631,289.345725 234.643631,289.003717 L234.643631,282 L237.75,282 L237.75,293.643123 L234.732385,293.643123 L234.732385,292.037175 C234.257977,292.72119 233.723343,293.219331 233.123201,293.530536 C232.524116,293.843866 231.883824,294 231.204438,294 C229.887929,294 228.919038,293.654806 228.297764,292.962294 C227.676491,292.271907 227.365854,291.183218 227.365854,289.695167 L227.365854,282 Z M247.302857,287.79726 C247.302857,287.06771 247.236513,286.482192 247.104931,286.043836 C246.973349,285.605479 246.761049,285.248532 246.466924,284.969863 C246.18828,284.706849 245.883098,284.535682 245.550273,284.455316 C245.216342,284.374951 244.88794,284.33529 244.562855,284.33529 C244.206809,284.33529 243.870667,284.4 243.552216,284.531507 C243.234871,284.663014 242.956227,284.86758 242.717389,285.145205 C242.477445,285.423875 242.287259,285.788128 242.147937,286.24214 C242.008615,286.694064 241.938953,287.235747 241.938953,287.863014 C241.938953,288.550815 242.005297,289.12381 242.136879,289.584083 C242.267356,290.044357 242.449801,290.41696 242.682005,290.701892 C242.914209,290.986823 243.185113,291.191389 243.495824,291.31559 C243.804323,291.439791 244.137148,291.502414 244.493193,291.502414 C245.267206,291.502414 245.901895,291.290541 246.398369,290.865753 C246.706868,290.573516 246.934649,290.2197 247.082817,289.803262 C247.229879,289.38578 247.302857,288.850359 247.302857,288.192825 L247.302857,287.79726 Z M247.372518,292.136986 C246.44481,293.380039 245.189804,294 243.61082,294 C241.907993,294 240.639719,293.460404 239.80268,292.379126 C238.966747,291.297847 238.54878,289.80013 238.54878,287.884932 C238.54878,286.993607 238.668199,286.179517 238.908143,285.441618 C239.149193,284.703718 239.493075,284.068102 239.942002,283.534768 C240.390929,283.001435 240.940478,282.589172 241.590648,282.29589 C242.240818,282.003653 242.97613,281.858578 243.796583,281.858578 C244.400312,281.858578 245.000724,281.959817 245.596714,282.165427 C246.191597,282.369993 246.753309,282.764514 247.279637,283.347945 L247.279637,278 L250.530488,278 L250.530488,293.649315 L247.372518,293.649315 L247.372518,292.136986 Z M252.17244,294 L255.278779,294 L255.278779,282.302521 L252.17244,282.302521 L252.17244,294 Z M252.128049,280.823529 L255.323171,280.823529 L255.323171,278 L252.128049,278 L252.128049,280.823529 Z M262.911585,282 C263.834419,282 264.666482,282.137184 265.408855,282.410521 C266.149067,282.685921 266.781219,283.079938 267.302068,283.591542 C267.82508,284.104177 268.22166,284.732336 268.493972,285.476019 C268.766283,286.220732 268.902439,287.054152 268.902439,287.978339 C268.902439,288.916968 268.763041,289.757607 268.482085,290.501289 C268.202209,291.246003 267.801306,291.877256 267.280456,292.397112 C266.758526,292.916968 266.126374,293.314079 265.385082,293.587416 C264.64487,293.862816 263.81929,294 262.911585,294 C261.005405,294 259.530384,293.480144 258.486523,292.440433 C257.442662,291.400722 256.920732,289.913357 256.920732,287.978339 C256.920732,287.054152 257.06121,286.220732 257.341086,285.476019 C257.620961,284.732336 258.021864,284.104177 258.543795,283.591542 C259.064645,283.079938 259.696796,282.685921 260.438089,282.410521 C261.179381,282.137184 262.00388,282 262.911585,282 Z M278.487805,278 L278.487805,280.890756 L273.75021,280.890756 L273.75021,294 L270.444912,294 L270.444912,280.890756 L265.707317,280.890756 L265.707317,278 L278.487805,278 Z M282.704475,282 C284.486615,282 285.827463,282.498195 286.724897,283.495616 C287.623393,284.490975 288.073171,285.833935 288.073171,287.523466 L288.073171,288.737494 L280.008988,288.737494 C280.038691,289.646209 280.276309,290.357916 280.721844,290.870552 C281.167379,291.383187 281.835682,291.638989 282.726752,291.638989 C283.959398,291.638989 284.688166,291.133574 284.910934,290.122744 L287.93951,290.122744 C287.731594,291.394533 287.208621,292.356885 286.36953,293.014956 C285.529378,293.671996 284.308401,294 282.704475,294 C280.773823,294 279.321592,293.480144 278.349901,292.440433 C277.37715,291.401753 276.890244,289.927798 276.890244,288.021661 C276.890244,287.068592 277.03133,286.220732 277.313502,285.476019 C277.595674,284.733368 277.993473,284.101083 278.504778,283.582259 C279.018204,283.062403 279.630284,282.668386 280.34314,282.401238 C281.055996,282.13409 281.843107,282 282.704475,282 Z M262.911585,284.424961 C262.00388,284.424961 261.326343,284.72821 260.881136,285.334709 C260.434847,285.942238 260.211163,286.823105 260.211163,287.978339 C260.211163,289.146983 260.430525,290.040227 260.869249,290.652914 C261.309054,291.266632 261.988752,291.574007 262.911585,291.574007 C263.81929,291.574007 264.496827,291.263538 264.943116,290.642599 C265.389404,290.021661 265.612008,289.133574 265.612008,287.978339 C265.612008,286.823105 265.389404,285.942238 264.943116,285.334709 C264.496827,284.72821 263.81929,284.424961 262.911585,284.424961 Z M282.637645,284.274368 C282.221812,284.274368 281.859019,284.33935 281.547145,284.469314 C281.23421,284.599278 280.971132,284.776689 280.75579,285.000516 C280.539387,285.224342 280.372842,285.48427 280.254033,285.779268 C280.135223,286.076328 280.068393,286.397112 280.053542,286.743682 L285.06581,286.743682 C285.06581,285.963899 284.868502,285.357401 284.474946,284.924188 C284.08139,284.490975 283.46931,284.274368 282.637645,284.274368 Z M289.670732,282.356877 L292.688347,282.356877 L292.688347,283.918216 C293.117322,283.263941 293.620257,282.779607 294.197154,282.467339 C294.774051,282.156134 295.380533,282 296.016599,282 C296.860813,282 297.547595,282.171004 298.080115,282.511949 C298.612636,282.855019 298.989837,283.35316 299.211721,284.007435 C299.744241,283.29368 300.306346,282.779607 300.898035,282.467339 C301.489724,282.156134 302.140583,282 302.851666,282 C304.04878,282 304.947937,282.331386 305.545966,282.993096 C306.146108,283.654806 306.445122,284.728625 306.445122,286.215613 L306.445122,294 L303.33981,294 L303.33981,286.639405 C303.33981,286.223048 303.301773,285.884227 303.227812,285.624004 C303.15385,285.364843 303.054531,285.159851 302.928797,285.011152 C302.803063,284.862454 302.650915,284.758364 302.473408,284.698885 C302.295901,284.639405 302.110998,284.609665 301.918699,284.609665 C301.608062,284.609665 301.322784,284.66171 301.064976,284.765799 C300.806112,284.869888 300.550418,285.048327 300.300006,285.300053 C300.091859,285.509294 299.924918,285.747212 299.80024,286.01487 C299.674506,286.282528 299.611111,286.565056 299.611111,286.862454 L299.611111,294 L296.504743,294 L296.504743,286.639405 C296.504743,286.223048 296.468819,285.884227 296.393801,285.624004 C296.31984,285.364843 296.219464,285.159851 296.094786,285.011152 C295.969052,284.862454 295.816904,284.758364 295.639397,284.698885 C295.46189,284.639405 295.276987,284.609665 295.084688,284.609665 C294.774051,284.609665 294.489829,284.66171 294.229909,284.765799 C293.971045,284.869888 293.716407,285.048327 293.464939,285.300053 C293.258904,285.509294 293.090907,285.757833 292.96623,286.048858 C292.840496,286.337759 292.7771,286.654275 292.7771,286.996283 L292.7771,294 L289.670732,294 L289.670732,282.356877 Z M311.021387,288.141036 C311.021387,288.885246 311.087603,289.478532 311.218931,289.922977 C311.350258,290.368462 311.562148,290.727557 311.855704,291.004424 C312.16471,291.26776 312.477026,291.43846 312.793758,291.518605 C313.111592,291.598751 313.432738,291.638303 313.757194,291.638303 C314.111447,291.638303 314.451354,291.57377 314.77581,291.442623 C315.100267,291.311475 315.381683,291.103305 315.622266,290.819152 C315.861746,290.534999 316.051564,290.166537 316.190616,289.715847 C316.329669,289.264117 316.398092,288.717668 316.398092,288.076503 C316.398092,287.406193 316.331876,286.845173 316.200549,286.392402 C316.069221,285.941712 315.888232,285.57325 315.656477,285.289097 C315.424723,285.004944 315.149928,284.800937 314.834301,284.677075 C314.51757,284.553214 314.180974,284.490762 313.825617,284.490762 C313.423909,284.490762 313.076277,284.542805 312.783825,284.644809 C312.489166,284.745772 312.203335,284.907104 311.92523,285.125683 C311.616224,285.401509 311.388884,285.751236 311.242106,286.173823 C311.094225,286.59745 311.021387,287.135571 311.021387,287.791309 L311.021387,288.141036 Z M307.8,282.349727 L310.952965,282.349727 L310.952965,283.857923 C311.878879,282.618267 313.145804,282 314.752635,282 C316.436717,282 317.695917,282.538121 318.531337,283.616445 C319.365653,284.69477 319.781707,286.173823 319.781707,288.053604 C319.781707,288.958106 319.662519,289.78038 319.42304,290.523549 C319.18356,291.26776 318.835928,291.904762 318.380144,292.436638 C317.924361,292.968514 317.371461,293.379651 316.722548,293.67109 C316.073635,293.96357 315.339746,294.108249 314.52088,294.108249 C313.918319,294.108249 313.320171,294.007286 312.725335,293.802238 C312.130498,293.598231 311.569873,293.191257 311.044563,292.579235 L311.044563,298 L307.8,298 L307.8,282.349727 Z M328.998737,286.743682 C328.998737,285.963899 328.801428,285.357401 328.407873,284.924188 C328.014317,284.490975 327.402237,284.274368 326.570571,284.274368 C326.154739,284.274368 325.791946,284.33935 325.480072,284.469314 C325.167136,284.599278 324.904059,284.776689 324.688717,285.000516 C324.472314,285.224342 324.305769,285.48427 324.186959,285.779268 C324.06815,286.076328 324.00132,286.397112 323.986469,286.743682 L328.998737,286.743682 Z M331.872437,290.122744 C331.664521,291.394533 331.141548,292.356885 330.302457,293.014956 C329.462305,293.671996 328.241327,294 326.637402,294 C324.70675,294 323.254519,293.480144 322.282828,292.440433 C321.310077,291.401753 320.823171,289.927798 320.823171,288.021661 C320.823171,287.068592 320.964257,286.220732 321.246429,285.476019 C321.528601,284.733368 321.9264,284.101083 322.437704,283.582259 C322.95113,283.062403 323.563211,282.668386 324.276066,282.401238 C324.988922,282.13409 325.776034,282 326.637402,282 C328.419541,282 329.760389,282.498195 330.657824,283.495616 C331.556319,284.490975 332.006098,285.833935 332.006098,287.523466 L332.006098,288.737494 L323.941915,288.737494 C323.971618,289.646209 324.209236,290.357916 324.654771,290.870552 C325.100306,291.383187 325.768608,291.638989 326.659678,291.638989 C327.892325,291.638989 328.621093,291.133574 328.84386,290.122744 L331.872437,290.122744 Z M332.804878,294 L336,294 L336,278 L332.804878,278 L332.804878,294 Z" id="Combined-Shape-Copy"></path>
                        </g>
                    </g>
                </svg>
            </div>
            <div class="title">
                <p><?php _e('Inloggen', 'tempel'); ?></p>
            </div>
        </div>
        <?php
    }
    
    
    /**
     * Dequeue the default login styles
     *
     * @since 1.0.0
     */
    function dequeue_login_styles()
    {
        wp_dequeue_style('login');
    }
    
    /**
     * Remove the admin bar bump callback action
     *
     * @since 1.0.0
     */
    public function remove_admin_bar_callback_action()
    {
        remove_action('wp_head', '_admin_bar_bump_cb');
    }
    
    /**
     *  Add the Studio Tempel logo to the admin bar
     *
     * @since 1.0.0
     */
    public function add_logo_to_admin_bar()
    {
        if (current_user_can('manage_options')) {
            global $wp_admin_bar;
            $wp_admin_bar->add_menu(array(
                'id' => 'studiotempel',
                'title' => '<img src="' . TMPL_PLUGIN_IMG_URL . 'admin-logo.svg' .  '" width="500" height="600" />',
                'href' => 'studiotempel.nl',
                'meta' => array(
                    'target' => '_blank', // Opens the link with a new tab
                    'title' => __('Studio Tempel'), // Text will be shown on hovering
                ),
            
            ));
        }
    }
    
    /**
     * Remove the WordPress logo from the admin bar
     *
     * @since 1.0.0
     */
    function remove_wp_logo_from_admin_bar($wp_admin_bar)
    {
        $wp_admin_bar->remove_node('wp-logo');
    }
    
    /**
     * Enqueue the admin bar styles
     *
     * @since 1.0.0
     */
    public function enqueue_admin_bar_theme()
    {
        wp_enqueue_style('admin-styles', TMPL_PLUGIN_CSS_URL . 'toolbar-theme.css');
    }
    
    
    /**
     * Enqueue the admin styles
     *
     * @since 1.0.0
     */
    public function enqueue_admin_theme()
    {
        wp_enqueue_style('admin-styles', TMPL_PLUGIN_CSS_URL . 'admin-theme.css');
        wp_enqueue_style('dashboard-widgets', TMPL_PLUGIN_CSS_URL . 'dashboard-widgets.css');
        wp_enqueue_script('admin', TMPL_PLUGIN_JS_URL . 'admin-theme.js', array('jquery'), null, true);
    }
    
    /**
     * Enqueue branding on the login screen
     *
     * @since 1.0.0
     */
    public function enqueue_login_theme()
    {
        if ($this->is_wplogin()) {
            wp_enqueue_script('login', TMPL_PLUGIN_JS_URL . 'login-screen.js', array('jquery'), null, true);
            wp_enqueue_style('login-styles-v2', TMPL_PLUGIN_CSS_URL . 'login-screen.css');
        }
    }
    
    /**
     * Helper funtcion to check if the current page is the login page
     *
     * @since 1.0.0
     */
    public function is_wplogin()
    {
        $ABSPATH_MY = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, ABSPATH);
        return ((in_array($ABSPATH_MY . 'wp-login.php', get_included_files()) || in_array($ABSPATH_MY . 'wp-register.php', get_included_files())) || (isset($_GLOBALS['pagenow']) && $GLOBALS['pagenow'] === 'wp-login.php') || $_SERVER['PHP_SELF'] == '/wp-login.php');
    }
    
    public function admin_footer_text()
    {
        echo '<span id="footer-thankyou">Developed by <a href="https://studiotempel.nl" target="_blank">Studio Tempel</a></span>';
    }
}
