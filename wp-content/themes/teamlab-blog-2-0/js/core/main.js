/***** Page Tracker *****/
function PageTrack(key) {
  try {
    if (typeof window.pageTracker == "undefined") {
      window.pageTracker = _gat._getTracker("UA-12442749-5");
    }

    if (window.pageTracker != null) {
      window.pageTracker._trackPageview(key);
    }
  } catch (err) {}
}

function PageEvent(category, key) {
  try {
    if (typeof window.pageTracker == "undefined") {
      window.pageTracker = _gat._getTracker("UA-12442749-5");
    }
    if (window.pageTracker != null) {
      window.pageTracker._trackEvent(category, key);
    }
  } catch (err) {}
}

function TrackEvent(category, action, label) {
  try {
    if (typeof window.pageTracker == "undefined") {
      window.pageTracker = _gat._getTracker("UA-12442749-5");
    }
    if (window.pageTracker != null) {
      window.pageTracker._trackEvent(category, action, label);
    }
  } catch (err) {}
}

/***** Language Selector *****/

var LanguageSelectorManager = new (function () {
  var openLngSelector = function () {
    var $lngSelector = jQuery("#LanguageSelector");
    if (!$lngSelector.hasClass("open")) {
      $lngSelector.find("ul.options:first").slideDown(10, function () {
        $lngSelector.addClass("open");
        jQuery(document).one("click", function () {
          $lngSelector.find("ul.options:first").hide();
          $lngSelector.removeClass("open");
        });
      });
    }
  };

  return {
    openLngSelector: openLngSelector,
  };
})();

/***** VALIDATE MAIN SEARCH *****/
$(".searchInput").focus(function () {
  $(this).closest(".searchForm").addClass("focus");
});

$(".searchInput").focusout(function () {
  if ($(this).val() == "") {
    $(this).closest(".searchForm").removeClass("focus");
  }
});

$(".searchInput").on("keyup", function () {
  if ($(this).val() != "") {
    $(this).closest(".searchForm").addClass("hasValue");
  } else {
    $(this).closest(".searchForm").removeClass("hasValue");
  }
});

$("#searchform").on("submit", function (event) {
  s = $("#headerInputSearch").val();
  s = s.replace(/^\s+|\s+$/g, "");
  if (!s) {
    event.preventDefault();
  }
});

$(".clearButton").on("click", function () {
  $(this).siblings(".searchInput").val("");
  $(this).closest(".searchForm").removeClass("focus").removeClass("hasValue");
});

/***** Suscribe input *****/
/*var $thisRecaptchaContainer;*/
var $subEmailInput = $("#subscribe-email-input");

var $inputBox = $("#InputBox");
var $loading = $(".inputButton");
var regex =
  /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i;

/*$(window).load(function() {
    $thisRecaptchaContainer = $(".recaptchaContainer");
});*/

function isValidEmail(value) {
  var valid = regex.test(value);
  return valid;
}

function SubmitSubEmail(inputValue) {
  /*var recaptchaResp = (typeof (window.grecaptcha) != "undefined") ? window.grecaptcha.getResponse(0) : ""; */

  if (ValidateInput(inputValue /*, recaptchaResp*/)) {
    var $thisInputContainer = $("#InputBox");
    var $urlImg = $(".inputButton").css("background-image");
    var $ValForButton = $(".inputButton").text();
    $(".inputButton").text("");
    $loading.addClass("change");
    $loading.css({
      "background-color": "#fda050",
      "background-image": "none",
    });

    $.ajax({
      type: "POST",
      url: window.wp_data.ajax_url,
      data: {
        action: "send_confirmation_email",
        email: inputValue /*, recaptchaResp : recaptchaResp*/,
      },
      dataType: "json",
      success: function (response) {
        if (response.errorMsg == "") {
          $loading.removeClass("change");
          $loading.css({
            "background-color": "#FF6F3D",
            "background-image": $urlImg,
          });
          $(".inputButton").text($ValForButton);
          showMsg();
        } else {
          $loading.removeClass("change");
          $loading.css({
            "background-color": "#FF6F3D",
            "background-image": $urlImg,
          });
          $(".inputButton").text($ValForButton);
          showErrors($thisInputContainer, response.errorMsg);
        }
      },
    });
  }
}

function showErrors($thisInputContainer, errorMsg) {
  $thisInputContainer.addClass("error");

  if (errorMsg == "Empty email") {
    $(".errorMessage.empty").show();
  } else if (errorMsg == "Email incorrect") {
    $(".errorMessage.incorrect").show();
  } else if (errorMsg == "Email is used") {
    $(".errorMessage.used").show();
  }
  /* else if(errorMsg == "Incorrect recaptcha"){
            $(".errorMessage.recaptcha").show();
        }*/
}

function showMsg() {
  $(".subscribe-blue").hide();
  $(".subscribe-white").show();
}

$subEmailInput.focus(function () {
  $inputBox.addClass("focus");
  $inputBox.removeClass("error");
  $(".errorMessage").hide();
});

$subEmailInput.focusout(function () {
  if ($(this).val() == "") {
    $inputBox.removeClass("focus");
  }
});

$subEmailInput.on("keyup", function () {
  if ($(this).val() != "") {
    $inputBox.addClass("hasValue");
  } else {
    $inputBox.removeClass("hasValue");
  }
});

$subEmailInput.keydown(function (e) {
  if (e.keyCode === 13) {
    SubmitSubEmail($subEmailInput.val());
  }
});

$("#email-sub-button").on("click", function () {
  SubmitSubEmail($subEmailInput.val());
});

/***** FORM SUBSCRIBE IN FOOTER *****/
var $subEmailInput2 = $("#subscribe-email-input2");

var $inputBox2 = $("#InputBox2");
var $loading = $(".inputButton");
var regex =
  /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i;

/*$(window).load(function() {
    $thisRecaptchaContainer = $(".recaptchaContainer");
});*/

function isValidEmail(value) {
  var valid = regex.test(value);
  return valid;
}

function SubmitSubEmail(inputValue) {
  /*var recaptchaResp = (typeof (window.grecaptcha) != "undefined") ? window.grecaptcha.getResponse(0) : ""; */

  if (ValidateInput(inputValue /*, recaptchaResp*/)) {
    var $thisInputContainer2 = $("#InputBox2");
    var $urlImg = $(".inputButton").css("background-image");
    var $ValForButton = $(".inputButton").text();
    $(".inputButton").text("");
    $loading.addClass("change");
    $loading.css({
      "background-color": "#fda050",
      "background-image": "none",
    });

    $.ajax({
      type: "POST",
      url: window.wp_data.ajax_url,
      data: {
        action: "send_confirmation_email",
        email: inputValue /*, recaptchaResp : recaptchaResp*/,
      },
      dataType: "json",
      success: function (response) {
        if (response.errorMsg == "") {
          $loading.removeClass("change");
          $loading.css({
            "background-color": "#FF6F3D",
            "background-image": $urlImg,
          });
          $(".inputButton").text($ValForButton);
          showMsg();
        } else {
          $loading.removeClass("change");
          $loading.css({
            "background-color": "#FF6F3D",
            "background-image": $urlImg,
          });
          $(".inputButton").text($ValForButton);
          showErrors($thisInputContainer2, response.errorMsg);
        }
      },
    });
  }
}

function ValidateInput(inputVal /*, recaptchaResp*/) {
  /*$(".errorMessage.recaptcha").hide();
    $inputBox.removeClass("error");*/

  correctValue = true;

  if (inputVal == "") {
    $inputBox.addClass("error");
    $inputBox2.addClass("error");
    $(".errorMessage.empty").show();
    correctValue = false;
  } else if (!isValidEmail(inputVal)) {
    $inputBox.addClass("error");
    $inputBox2.addClass("error");
    $(".errorMessage.incorrect").show();
    correctValue = false;
  } else {
    /*else if(recaptchaResp == "" || recaptchaResp == undefined){
           $inputBox.addClass("error");
           $(".errorMessage.recaptcha").show();
           correctValue=false
       }*/
    $inputBox.addClass("valid");
    $inputBox2.addClass("valid");
  }

  return correctValue;
}

function showErrors($thisInputContainer2, errorMsg) {
  $thisInputContainer2.addClass("error");

  if (errorMsg == "Empty email") {
    $(".errorMessage.empty").show();
  } else if (errorMsg == "Email incorrect") {
    $(".errorMessage.incorrect").show();
  } else if (errorMsg == "Email is used") {
    $(".errorMessage.used").show();
  }
  /* else if(errorMsg == "Incorrect recaptcha"){
            $(".errorMessage.recaptcha").show();
        }*/
}

function showMsg() {
  $(".subscribe-blue").hide();
  $(".subscribe-white").show();
}

$subEmailInput2.focus(function () {
  $inputBox2.addClass("focus");
  $inputBox2.removeClass("error");
  $(".errorMessage").hide();
});

$subEmailInput2.focusout(function () {
  if ($(this).val() == "") {
    $inputBox2.removeClass("focus");
  }
});

$subEmailInput2.on("keyup", function () {
  if ($(this).val() != "") {
    $inputBox2.addClass("hasValue");
  } else {
    $inputBox2.removeClass("hasValue");
  }
});

$subEmailInput2.keydown(function (e) {
  if (e.keyCode === 13) {
    SubmitSubEmail($subEmailInput2.val());
  }
});

$("#email-sub-button2").on("click", function () {
  SubmitSubEmail($subEmailInput2.val());
});

/***** Popup for subcribe *****/
$("#subscribelink, #subscribelink2").click(function () {
  var html = document.documentElement;
  var body = document.body;

  var scrollTop = html.scrollTop || (body && body.scrollTop) || 0;
  scrollTop -= html.clientTop; // в IE7- <html> смещён относительно (0,0)

  $(".hidden").css("top", scrollTop);
  $(".hidden").show();
});

$(".close-popup").click(function () {
  $(".hidden").hide();
});

// $("a[href=''], a:not([href])").css("cursor", "default");

/***** Click for header *****/
$("#customer_stories_div").on("click", function () {
  location.href = $("#navitem_features_customer_stories").attr("href");
});

$("#compare_div").on("click", function () {
  location.href = $("#navitem_download_compare").attr("href");
});

$("#reseller_div").on("click", function () {
  location.href = $("#navitem_prices_reseller").attr("href");
});

$("#oforms_div").on("click", function () {
  location.href = $("#navitem_features_oforms").attr("href");
});
$("#for_developers_div").on("click", function () {
  location.href = $("#navitem_developers_for_developers").attr("href");
});
$("#for_business_div").on("click", function () {
  location.href = $("#navitem_education_for_business").attr("href");
});
$("#education_eve_div").on("click", function () {
  location.href = $("#navitem_education_eve_events").attr("href");
});

$(".overlay-trigger").click(function (event) {
  event.preventDefault();
  $("#expose-mask").fadeIn(100, function () {
    $(".overlay-box").css({ display: "block" });
  });
});

$(".img-popup").on("click", function () {
  $src = $(this).attr("src");
  $(".overlay-dark").css("display", "block");
  $(".overlay-padding").css("opacity", 1);
  $(".img-overlay").attr("src", $src);
  $(".overlay-padding").css("transform", "translate(-50%, -50%) scale(1, 1)");
});

$(".overlay-dark, .close-overlay").on("click", function () {
  $(".overlay-dark").css("display", "none");
  $(".overlay-padding").css("opacity", 0);
  $src = "";
  setTimeout(function () {
    $(".overlay-padding").css("transform", "translate(-50%, 0) scale(0, 0)");
  }, 100);
});

if ($("#comments").length) {
  $("#comments .comment-wrap:first").css("border-top", "none");
  $("#comments .comment.depth-1:last").css("border-bottom", "1px solid #E0E0E0");
  if ($("#comments").length) {
      $("#comments").after($("<ul style='list-style: none;padding:0;'></ul>").append($(
          "#recent-posts")));
      $("#recent-posts").show();
  }
}

window.onload = function () {
  var $adventAnnounce = $(".advent-announce");
  var $header = $("header");
  var $body = $("main");
  var top, bannerHeight;

  $(window).on("scroll", function () {
    top = $(window).scrollTop();
    $adventAnnounce.each(function () {
      if ($(this).css("display") != "none") {
        bannerHeight = $(this).outerHeight();
      }
    });
    if (top >= bannerHeight) {
      $header.addClass("onscrolling");
      $body.css("top", $header.height());
      $body.css("marginBottom", $header.height());
    } else {
      $header.removeClass("onscrolling");
      $body.css("top", "");
      $body.css("marginBottom", "");
    }
  });
};

let nameCommentInput = $("#comments #author"),
    emailCommentInput = $("#comments #email"),
    messageCommentInput = $("#comments #comment"),
    dataItemInput = $("#comments .data-input"),
    commentSubmitButton = $("#comments #commentformsubmit"),
    gRecaptcha = $("#comments .g-recaptcha"),
    gRecaptchaError = $("#comments .g-recaptcha-error");

function inputsCommentValidate() {
  if (nameCommentInput.val().length > 0 && isValidEmail(emailCommentInput.val()) && messageCommentInput.val().length > 0 && gRecaptcha.hasClass("active")) {
    commentSubmitButton.removeAttr("disabled");
  } else{
    commentSubmitButton.attr("disabled", true);
  }
};

function recaptchaCallback() {
  gRecaptcha.addClass("active");
  gRecaptchaError.removeClass("error");
  inputsCommentValidate();
};

dataItemInput.on("focus", function() {
  $(this).parent().removeClass("error");
});

dataItemInput.on("blur", function() {
  const thisParent = $(this).parent();

  if (!this.value) {
    thisParent.addClass("error");
  }

  if ($(this).is("#email")) {
    if (!this.value) {
      thisParent.addClass("error");
      thisParent.removeClass("incorrect");
      isValid = false;
    } else if (!isValidEmail($.trim($(this)[0].value))) {
      thisParent.removeClass("error");
      thisParent.addClass("incorrect");
      isValid = false;
    } else {
      thisParent.removeClass("error");
      thisParent.removeClass("incorrect");
    }
  }

  inputsCommentValidate();
});

dataItemInput.on("keyup", function() {
  const thisParent = $(this).parent();

  if (!this.value) {
    thisParent.addClass("error");
  }

  if ($(this).is("#email")) {
    if (!this.value) {
      thisParent.addClass("error");
      thisParent.removeClass("incorrect");
      isValid = false;
    } else if (!isValidEmail($.trim($(this)[0].value))) {
      thisParent.removeClass("error");
      thisParent.addClass("incorrect");
      isValid = false;
    } else {
      thisParent.removeClass("error");
      thisParent.removeClass("incorrect");
    }
  }

  inputsCommentValidate();
});

commentSubmitButton.on("click", function (e) {
  let isValid = true, data = {
    name: $.trim(nameCommentInput.val()),
    email: $.trim(emailCommentInput.val()),
    message: $.trim(messageCommentInput.val()),
  };
  
  if (data.name == "") {
    nameCommentInput.parent().addClass("error");
    isValid = false;
  }
  
  if (data.email == "") {
    emailCommentInput.parent().addClass("error");
    emailCommentInput.parent().removeClass("incorrect");
    isValid = false;
  } else if (!isValidEmail(data.email)) {
    emailCommentInput.parent().removeClass("error");
    emailCommentInput.parent().addClass("incorrect");
    isValid = false;
  } else {
    emailCommentInput.parent().removeClass("error");
    emailCommentInput.parent().removeClass("incorrect");
  }
  
  if (data.message == "") {
    messageCommentInput.parent().addClass("error");
    isValid = false;
  }

  if (!gRecaptcha.hasClass("active")) {
    gRecaptchaError.addClass("error");
    isValid = false;
  }

  if (!isValid) {
    e.preventDefault();
    return;
  }
});