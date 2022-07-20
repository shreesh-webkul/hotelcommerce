{*
* 2010-2022 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through this link for complete license : https://store.webkul.com/license.html
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to https://store.webkul.com/customisation-guidelines/ for more information.
*
*  @author    Webkul IN <support@webkul.com>
*  @copyright 2010-2022 Webkul IN
*  @license   https://store.webkul.com/license.html
*}

<style>
{literal}
@-webkit-keyframes placeHolderShimmer{0%{background-position:-800px 0}100%{background-position:800px 0}}
@keyframes placeHolderShimmer{0%{background-position:-800px 0}100%{background-position:800px 0}}
{/literal}
    #suggestion-wrapper-placeholder .panel {
        text-align: center
    }
    #suggestion-wrapper-placeholder .animate-content {
        margin: auto;
        background: #f6f7f8;
        background: #eee;
        background: -webkit-gradient(linear, left top, right top, color-stop(8%, #eee), color-stop(18%, #ddd), color-stop(33%, #eee));
        background: linear-gradient(to right, #eee 8%, #ddd 18%, #eee 33%);
        background-size: 800px 104px;
        -webkit-animation-name: placeHolderShimmer;
        animation-name: placeHolderShimmer;
        -webkit-animation-duration: 1.75s;
        animation-duration: 1.75s;
        -webkit-animation-timing-function: linear;
        animation-timing-function: linear;
        -webkit-animation-iteration-count: infinite;
        animation-iteration-count: infinite;
        -webkit-animation-fill-mode: forwards;
        animation-fill-mode: forwards;
    }
    #suggestion-wrapper-placeholder .animate-content-bar {
        width: 100%;
        height: 10px;
        margin-bottom: 10px;
    }

    #suggestion-wrapper-placeholder .animate-content-box {
        width: 100%;
        height: 50px;
        margin-bottom: 10px;
    }

    #suggestion-wrapper-placeholder .banner1 {
    }
    #suggestion-wrapper-placeholder .banner2 {
    }
</style>
<div class="row" id="suggestion-wrapper-placeholder">

    <div class="banner banner1 panel">
        <div class="row">
            <div class="col-sm-12">
                <div class="animate-content animate-content-bar"></div>
                <div class="animate-content animate-content-bar"></div>
            </div>
        </div>
    </div>
    <div class="banner banner2 panel">
        <div class="row">
            <div class="col-sm-4">
                <div class="animate-content animate-content-box"></div>
            </div>
            <div class="col-sm-8">
                <div class="animate-content animate-content-bar"></div>
                <div class="animate-content animate-content-bar"></div>
                <div class="animate-content animate-content-bar"></div>
            </div>
        </div>
    </div>
</div>

<div class="row" id="suggestion-wrapper" style="display:none">
</div>