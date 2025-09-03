jQuery(document).ready(function ($) {
  // control birth_date when it changes with datepicker
  $(window).on("cx-control-change", function (event) {
    if (event.controlName !== "birth_date") {
      return;
    }
    const birthDateVal = event.controlStatus;
    if (!birthDateVal) {
      return;
    }
    const formatDate = formatBirthDate(birthDateVal);
    if (!formatDate) {
      return;
    }
    const [year, month, day] = formatDate.split("-");
    if (year && month && day) {
      const interval = calculateAge(year, month, day);
      let displayDate = "";
      let hasContent = false;
      if (interval.years > 0) {
        displayDate += `${hasContent ? " " : ""}${interval.years} años`;
        hasContent = true;
      }
      if (interval.months > 0) {
        displayDate += `${hasContent ? ", " : ""}${interval.months} meses`;
        hasContent = true;
      }
      if (interval.days > 0) {
        displayDate += `${hasContent ? " y " : ""}${interval.days} días`;
      }
      $("#calculated_age").val(displayDate);
    }
  });
  $("#birth_date").change(function () {
    var birthDateVal = $(this).val();
    if (!birthDateVal) {
      return;
    }
    const formatDate = formatBirthDate(birthDateVal);
    if (!formatDate) {
      return;
    }
    const [year, month, day] = formatDate.split("-");
    if (year && month && day) {
      const interval = calculateAge(year, month, day);
      let displayDate = "";
      let hasContent = false;
      if (interval.years > 0) {
        displayDate += `${hasContent ? " " : ""}${interval.years} años`;
        hasContent = true;
      }
      if (interval.months > 0) {
        displayDate += `${hasContent ? ", " : ""}${interval.months} meses`;
        hasContent = true;
      }
      if (interval.days > 0) {
        displayDate += `${hasContent ? " y " : ""}${interval.days} días`;
      }
      $("#calculated_age").val(displayDate);
    }
  });
  const birthDateVal = $("#birth_date").val();
  if (birthDateVal) {
    // fire change event
    $("#birth_date").trigger("change");
  }

  function calculateAge(birthYear, birthMonth, birthDay) {
    const birthDate = new Date(birthYear, birthMonth - 1, birthDay);
    const today = new Date();

    let years = today.getFullYear() - birthDate.getFullYear();
    let months = today.getMonth() - birthDate.getMonth();
    let days = today.getDate() - birthDate.getDate();

    if (days < 0) {
      months--;
      days += new Date(today.getFullYear(), today.getMonth(), 0).getDate();
    }

    if (months < 0) {
      years--;
      months += 12;
    }

    return { years, months, days };
  }

  $("#calculated_age").change(function () {
    var calculatedAgeVal = $(this).val();
    if (!calculatedAgeVal) {
      return;
    }
    if (isNaN(calculatedAgeVal)) {
      return;
    }
    const age = parseInt(calculatedAgeVal);
    if (age < 0) {
      return;
    }
    const today = new Date();
    const birthYear = today.getFullYear() - age;
    const birthMonth = ("0" + (today.getMonth() + 1)).slice(-2);
    const birthDay = ("0" + today.getDate()).slice(-2);
    $("#birth_date").val(`${birthYear}-${birthMonth}-${birthDay}`);
    $("#birth_date")
      .next(`input[type="text"].cx-ui-text`)
      .datepicker("setDate", `${birthYear}-${birthMonth}-${birthDay}`);
  });

  function formatBirthDate(birthDateVal) {
    // Check if date is in YYYY-MM-DD format
    const isoFormat = /^\d{4}-\d{2}-\d{2}$/;
    if (isoFormat.test(birthDateVal)) {
      return birthDateVal;
    }

    // Check if date is in DD/MM/YYYY format
    const ddmmyyyy = /^(\d{2})\/(\d{2})\/(\d{4})$/;
    if (ddmmyyyy.test(birthDateVal)) {
      const [_, day, month, year] = birthDateVal.match(ddmmyyyy);
      return `${year}-${month}-${day}`;
    }

    // Invalid format
    return "";
  }
});
