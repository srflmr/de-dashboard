// Client-side form helpers and UI bindings.
function toggleForm(formId) {
    var form = document.getElementById(formId);
    if (form) {
        form.classList.toggle("show");
    }
}
function getLangMessage(key, fallback) {
    if (typeof appLang !== "undefined" && appLang && appLang[key]) {
        return appLang[key];
    }
    return fallback;
}

// Validate rule form fields.
function validateRuleForm(form) {
    var customerId     = form.querySelector("[name=\"customer_id\"]").value;
    var ruleName       = form.querySelector("[name=\"rule_name\"]").value.trim();
    var detectionLogic = form.querySelector("[name=\"detection_logic\"]").value.trim();
    var createdBy      = form.querySelector("[name=\"created_by\"]").value.trim();
    var modifiedBy     = form.querySelector("[name=\"modified_by\"]").value.trim();

    if (customerId === "") {
        alert(getLangMessage("requiredCustomer", "Customer is required."));
        form.querySelector("[name=\"customer_id\"]").focus();
        return false;
    }
    if (ruleName === "") {
        alert(getLangMessage("requiredRuleName", "Rule name is required."));
        form.querySelector("[name=\"rule_name\"]").focus();
        return false;
    }
    if (ruleName.length > 200) {
        alert(getLangMessage("maxRuleName", "Rule name must not exceed 200 characters."));
        return false;
    }
    if (detectionLogic === "") {
        alert(getLangMessage("requiredDetectionLogic", "Detection logic is required."));
        form.querySelector("[name=\"detection_logic\"]").focus();
        return false;
    }
    if (createdBy === "") {
        alert(getLangMessage("requiredCreatedBy", "Created By is required."));
        form.querySelector("[name=\"created_by\"]").focus();
        return false;
    }
    if (modifiedBy === "") {
        alert(getLangMessage("requiredModifiedBy", "Modified By is required."));
        form.querySelector("[name=\"modified_by\"]").focus();
        return false;
    }
    return true;
}

function validateSiemForm(form) {
    var platformName = form.querySelector("[name=\"siem_name\"]");
    if (platformName && platformName.value.trim() === "") {
        alert(getLangMessage("requiredPlatform", "Platform name is required."));
        platformName.focus();
        return false;
    }
    return true;
}

function validateCustomerForm(form) {
    var customerName = form.querySelector("[name=\"customer_name\"]");
    var siemId       = form.querySelector("[name=\"siem_id\"]");
    if (customerName && customerName.value.trim() === "") {
        alert(getLangMessage("requiredCustName", "Customer name is required."));
        customerName.focus();
        return false;
    }
    if (siemId && siemId.value === "") {
        alert(getLangMessage("requiredCustSiem", "SIEM platform is required."));
        siemId.focus();
        return false;
    }
    return true;
}

// Format ISO date string for UI labels.
function formatDate(dateString) {
    if (!dateString || dateString === "0000-00-00") {
        return "-";
    }
    var date = new Date(dateString);
    if (isNaN(date.getTime())) {
        return dateString;
    }
    return date.toLocaleDateString("en-GB", {
        day: "numeric",
        month: "short",
        year: "numeric"
    });
}

document.addEventListener("DOMContentLoaded", function() {

    // Toggle inline forms.
    var toggleBtns = document.querySelectorAll("[data-toggle]");
    for (var i = 0; i < toggleBtns.length; i++) {
        toggleBtns[i].addEventListener("click", function() {
            toggleForm(this.getAttribute("data-toggle"));
        });
    }

    // Validate SIEM forms.
    var siemForms = document.querySelectorAll("form[data-validate=\"siem\"]");
    for (var i = 0; i < siemForms.length; i++) {
        siemForms[i].addEventListener("submit", function(e) {
            if (!validateSiemForm(this)) { e.preventDefault(); }
        });
    }

    // Validate customer forms.
    var custForms = document.querySelectorAll("form[data-validate=\"customer\"]");
    for (var i = 0; i < custForms.length; i++) {
        custForms[i].addEventListener("submit", function(e) {
            if (!validateCustomerForm(this)) { e.preventDefault(); }
        });
    }

    // Validate rule forms.
    var ruleForms = document.querySelectorAll("form[data-validate=\"rule\"]");
    for (var i = 0; i < ruleForms.length; i++) {
        ruleForms[i].addEventListener("submit", function(e) {
            if (!validateRuleForm(this)) { e.preventDefault(); }
        });
    }

    // Confirm delete actions.
    var deleteForms = document.querySelectorAll("form[data-confirm]");
    for (var i = 0; i < deleteForms.length; i++) {
        deleteForms[i].addEventListener("submit", function(e) {
            var itemName = this.getAttribute("data-confirm") || "item";
            var template = getLangMessage("confirmDelete", "Are you sure you want to delete \"%s\"?");
            var msg = template.replace("%s", itemName);
            if (!confirm(msg)) { e.preventDefault(); }
        });
    }

    // Render localized date labels.
    var dateElements = document.querySelectorAll(".format-date");
    for (var i = 0; i < dateElements.length; i++) {
        var rawDate = dateElements[i].textContent.trim();
        dateElements[i].textContent = formatDate(rawDate);
    }
});