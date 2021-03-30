/*
code based on https://github.com/stripe-samples/accept-a-card-payment/blob/master/without-webhooks/client/web/script.js
MIT licensed
*/

// A reference to Stripe.js
var stripe = Stripe('pk_test_ZJVPMHL96nEnOg1DrpEDJMzJ000cg3ZDP9');

let publishable_key = "pk_test_ZJVPMHL96nEnOg1DrpEDJMzJ000cg3ZDP9";
let secret_key = "sk_test_3zDeYaSHfSa29EPMmpuu0aGH00erivCJmC";

let keys = {STRIPE_PUBLISHABLE_KEY: "pk_test_ZJVPMHL96nEnOg1DrpEDJMzJ000cg3ZDP9", STRIPE_SECRET_KEY: "sk_test_3zDeYaSHfSa29EPMmpuu0aGH00erivCJmC"};

var orderData = {
  items: [{ id: "photo-subscription" }],
  currency: "usd"
};

function start_payment_init() {
  // Disable the button until we have Stripe set up on the page
  const submit_btn = document.querySelector("#submit");

  if (submit_btn != null) {
    submit_btn.disabled = true;
  } else {
    setTimeout(loop(true), 500);
  }

  fetch("/modules/invoice_wizard/key.php")
    .then(function(result) {
      return result.text();
    })
    .then(function(data) {
      console.log(data);
      //return setupElements(data);
    })
    // .then(function({ stripe, card, clientSecret }) {
    //   if (submit_btn != null) {
    //     submit_btn.disabled = false;
    //   } else {
    //     setTimeout(loop(false), 500)
    //   }

      var form = document.getElementById("payment-form");
      form.addEventListener("submit", function(event) {
        event.preventDefault();
        pay(stripe, card, clientSecret);
      });
    //});
}

function loop(type=false) {
  const submit_btn = document.querySelector("#submit");
  if (submit_btn != null) {
    submit_btn.disabled = type;
  } else {
    setTimeout(loop(type), 500);
  }
}

var setupElements = function(data) {
  stripe = Stripe(data.publishableKey);
  /* ------- Set up Stripe Elements to use in checkout form ------- */
  var elements = stripe.elements();
  var style = {
    base: {
      color: "#32325d",
      fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
      fontSmoothing: "antialiased",
      fontSize: "16px",
      "::placeholder": {
        color: "#aab7c4"
      }
    },
    invalid: {
      color: "#fa755a",
      iconColor: "#fa755a"
    }
  };

  var card = elements.create("card", { style: style });
  card.mount("#card-element");

  return {
    stripe: stripe,
    card: card,
    clientSecret: data.clientSecret
  };
};

var handleAction = function(clientSecret) {
  stripe.handleCardAction(clientSecret).then(function(data) {
    if (data.error) {
      showError("Your card was not authenticated, please try again");
    } else if (data.paymentIntent.status === "requires_confirmation") {
      fetch("/pay", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          paymentIntentId: data.paymentIntent.id
        })
      })
        .then(function(result) {
          return result.json();
        })
        .then(function(json) {
          if (json.error) {
            showError(json.error);
          } else {
            orderComplete(clientSecret);
          }
        });
    }
  });
};

/*
 * Collect card details and pay for the order
 */
var pay = function(stripe, card) {
  changeLoadingState(true);

  // Collects card details and creates a PaymentMethod
  stripe
    .createPaymentMethod("card", card)
    .then(function(result) {
      if (result.error) {
        showError(result.error.message);
      } else {
        orderData.paymentMethodId = result.paymentMethod.id;

        return fetch("/pay", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify(orderData)
        });
      }
    })
    .then(function(result) {
      return result.json();
    })
    .then(function(response) {
      if (response.error) {
        showError(response.error);
      } else if (response.requiresAction) {
        // Request authentication
        handleAction(response.clientSecret);
      } else {
        orderComplete(response.clientSecret);
      }
    });
};

/* ------- Post-payment helpers ------- */

/* Shows a success / error message when the payment is complete */
var orderComplete = function(clientSecret) {
  stripe.retrievePaymentIntent(clientSecret).then(function(result) {
    var paymentIntent = result.paymentIntent;
    var paymentIntentJson = JSON.stringify(paymentIntent, null, 2);

    document.querySelector(".sr-payment-form").classList.add("hidden");
    document.querySelector("pre").textContent = paymentIntentJson;

    document.querySelector(".sr-result").classList.remove("hidden");
    setTimeout(function() {
      document.querySelector(".sr-result").classList.add("expand");
    }, 200);

    changeLoadingState(false);
  });
};

var showError = function(errorMsgText) {
  changeLoadingState(false);
  var errorMsg = document.querySelector(".sr-field-error");
  errorMsg.textContent = errorMsgText;
  setTimeout(function() {
    errorMsg.textContent = "";
  }, 4000);
};

// Show a spinner on payment submission
var changeLoadingState = function(isLoading) {
  if (isLoading) {
    document.querySelector("button").disabled = true;
    document.querySelector("#spinner").classList.remove("hidden");
    document.querySelector("#button-text").classList.add("hidden");
  } else {
    document.querySelector("button").disabled = false;
    document.querySelector("#spinner").classList.add("hidden");
    document.querySelector("#button-text").classList.remove("hidden");
  }
};
