jQuery(document).ready(function ($) {
  new Vue({
    el: "#wcfm-app",
    data: {
      formData: {
        ID: 0,
        name: "",
        status: "publish",
        meta: {
          color: "#0e76a8",
        },
      },
      apbPostMeta: {
        ID: 0,
        custom_schedule: {
          default_slot: 1800,
          booking_type: "slot",
          working_days: [],
          working_hours: {
            monday: [],
            tuesday: [],
            wednesday: [],
            thursday: [],
            friday: [],
            saturday: [],
            sunday: [],
          },
          use_custom_schedule: true,
          working_days_mode: "override_days",
        },
        meta_settings: {},
      },
      today: moment().utc().startOf("day"),
      dateMomentFormat: "DD-MM-YYYY",
      workingHours: {
        monday: [],
        tuesday: [],
        wednesday: [],
        thursday: [],
        friday: [],
        saturday: [],
        sunday: [],
      },
      daysLabels: {
        monday: "Lunes",
        tuesday: "Martes",
        wednesday: "Miércoles",
        thursday: "Jueves",
        friday: "Viernes",
        saturday: "Sábado",
        sunday: "Domingo",
      },
      editDay: false,
      workingDayIndex: -1,
      workingDayData: {
        editIndex: "",
        end: "",
        endTimeStamp: "",
        name: "",
        start: "",
        startTimeStamp: "",
        type: "working_days",
        schedule: [],
      },
    },
    computed: {
      pageTitle() {
        return `Horario de atención ${this.formData.name}`;
      },
      isUpdate() {
        return this.formData.ID > 0;
      },
    },
    created() {
      const defaultApb = JSON.parse(
        document.getElementById("wcfm-app").dataset.defaultApb
      );
      // check if working hours exists if not create them empty
      if (!defaultApb.custom_schedule.working_hours) {
        defaultApb.custom_schedule.working_hours = {};
      }
      for (const day in this.workingHours) {
        if (!defaultApb.custom_schedule.working_hours[day]) {
          defaultApb.custom_schedule.working_hours[day] = [];
        }
      }
      if (!defaultApb.custom_schedule.working_days) {
        defaultApb.custom_schedule.working_days = [];
      }
      this.apbPostMeta = defaultApb;

      this.formData = JSON.parse(
        document.getElementById("wcfm-app").dataset.formData
      );
    },
    mounted() {
      const self = this;
      $("#wcfm-calendar").fullCalendar({
        header: {
          left: "prev,next today",
          center: "title",
          right: "month,agendaWeek,agendaDay",
          ignoreTimezone: false,
        },
        timeFormat: "HH:mm",
        displayEventEnd: true,
        locale: "es",
        events: function (start, end, timezone, callback) {
          callback(
            self.apbPostMeta.custom_schedule.working_days
              .filter(
                (event) =>
                  event.start === event.end &&
                  event.startTimeStamp >= self.today.valueOf()
              )
              .flatMap((day, index) => {
                const startEndDate = moment(day.start, self.dateMomentFormat);
                const startEndFormat = startEndDate.format("YYYY-MM-DD");
                let counter = 0;
                if (!day.schedule) {
                  return [];
                }
                return day.schedule.map((slot) => {
                  const start = moment(
                    `${startEndFormat} ${slot.from}`,
                    "YYYY-MM-DD HH:mm"
                  );
                  const end = moment(
                    `${startEndFormat} ${slot.to}`,
                    "YYYY-MM-DD HH:mm"
                  );
                  counter++;
                  return {
                    start: start.format("YYYY-MM-DD HH:mm:ss"),
                    end: end.format("YYYY-MM-DD HH:mm:ss"),
                  };
                });
              })
          );
        },
        dayClick: function (date, jsEvent, view) {
          self.handleDate(date);
        },
        eventClick: function (calEvent, jsEvent, view) {
          self.handleDate(calEvent.start);
        },
        validRange: {
          start: self.today.format("YYYY-MM-DD"),
        },
      });
    },
    methods: {
      handleDate(date) {
        const startEndDate = this.parseDate(date);
        const newDate = new Date(date.year(), date.month(), date.date());

        this.workingDayIndex =
          this.apbPostMeta.custom_schedule.working_days.findIndex(
            (day) => day.start === startEndDate && day.end === startEndDate
          );
        if (-1 === this.workingDayIndex) {
          this.workingDayData.name = startEndDate;
          this.workingDayData.start = startEndDate;
          this.workingDayData.end = startEndDate;
          this.workingDayData.startTimeStamp = this.dateToTimestamp(newDate);
          this.workingDayData.endTimeStamp = this.dateToTimestamp(newDate);
          this.workingDayData.schedule = [];
        } else {
          const cloneWorkingDay = JSON.parse(
            JSON.stringify(
              this.apbPostMeta.custom_schedule.working_days[
                this.workingDayIndex
              ]
            )
          );
          if (!cloneWorkingDay.schedule) {
            cloneWorkingDay.schedule = [];
          }
          this.$set(this, "workingDayData", cloneWorkingDay);
        }
        this.editDay = true;
      },
      newHourSlot(dayName) {
        const newSlot = {
          from: "09:00",
          to: "18:00",
        };

        if (
          0 < this.apbPostMeta.custom_schedule.working_hours[dayName].length
        ) {
          let lastSlot =
            this.apbPostMeta.custom_schedule.working_hours[dayName][
              this.apbPostMeta.custom_schedule.working_hours[dayName].length - 1
            ];

          lastSlot = lastSlot.to.split(":");

          let lastFrom = parseInt(lastSlot[0], 10);
          let newFrom = lastFrom + 1;
          let newTo = newFrom + 1;

          if (23 <= newFrom) {
            newFrom = 23;
          }

          if (23 <= newTo) {
            newTo = 23;
          }

          newSlot.from = newFrom + ":" + lastSlot[1];
          newSlot.to = newTo + ":" + lastSlot[1];
        }

        this.apbPostMeta.custom_schedule.working_hours[dayName].push(newSlot);
      },
      deleteHourSlot(dayName, index) {
        this.apbPostMeta.custom_schedule.working_hours[dayName].splice(
          index,
          1
        );
      },
      onUpdateTimeSettings: function (valueObject) {
        var timeStamp = moment.duration(valueObject.value).asSeconds();

        if ("default_slot" === valueObject.key && timeStamp < 60) {
          this.$CXNotice.add({
            message: "La duración del slot no puede ser menor a un minuto",
            type: "error",
            duration: 7000,
          });

          timeStamp = 60;
        }
        this.apbPostMeta.custom_schedule[valueObject.key] = timeStamp;
      },
      getTimeSettings: function (key) {
        var dateObject = moment.duration(
            parseInt(this.apbPostMeta.custom_schedule[key]),
            "seconds"
          ),
          minutes =
            dateObject._data.minutes < 10
              ? `0${dateObject._data.minutes}`
              : dateObject._data.minutes,
          hours =
            dateObject._data.hours < 10
              ? `0${dateObject._data.hours}`
              : dateObject._data.hours;

        return `${hours}:${minutes}`;
      },
      dateToTimestamp: function (date) {
        return Date.UTC(
          date.getFullYear(),
          date.getMonth(),
          date.getDate(),
          0,
          0,
          0
        );
      },
      parseDate: function (date, format = this.dateMomentFormat) {
        return moment(date).format(format);
      },
      newDaySlot: function () {
        const newSlot = {
          from: "09:00",
          to: "18:00",
        };
        this.workingDayData.schedule.push(newSlot);
      },
      setSchedule(value, index, key) {
        let current = this.workingDayData.schedule[index];
        current[key] = value;
        this.workingDayData.schedule.splice(index, 1, current);
      },
      deleteDaySlot: function (index) {
        this.workingDayData.schedule.splice(index, 1);
      },
      handleDayOk: function () {
        if (!this.workingDayData.endTimeStamp) {
          this.workingDayData.endTimeStamp = this.workingDayData.startTimeStamp;
        }

        if (
          !this.workingDayData.start ||
          this.workingDayData.startTimeStamp > this.workingDayData.endTimeStamp
        ) {
          this.$CXNotice.add({
            message: wp.i18n.__(
              "Date is not correct",
              "jet-appointments-booking"
            ),
            type: "error",
            duration: 7000,
          });

          return;
        }

        if (this.workingDayIndex === -1) {
          this.apbPostMeta.custom_schedule.working_days.push(
            JSON.parse(JSON.stringify(this.workingDayData))
          );
        } else {
          // update schedule with is an array
          this.apbPostMeta.custom_schedule.working_days[
            this.workingDayIndex
          ].schedule = JSON.parse(JSON.stringify(this.workingDayData.schedule));
        }

        $("#wcfm-calendar").fullCalendar("refetchEvents");
        this.handleDayCancel();
        this.submitForm();
      },
      handleDayCancel: function () {
        this.editDay = false;
      },
      submitForm() {
        if (!this.formData.name) {
          this.$CXNotice.add({
            message: "El nombre es requerido",
            type: "error",
            duration: 7000,
          });
          return;
        }
        if (!this.apbPostMeta.custom_schedule.default_slot) {
          this.$CXNotice.add({
            message: "La duración del slot es requerida",
            type: "error",
            duration: 7000,
          });
          return;
        }
        $("#wcfm-content").block({
          message: null,
          overlayCSS: {
            background: "#fff",
            opacity: 0.6,
          },
        });

        const self = this;
        wp.apiFetch({
          method: "POST",
          path: this.isUpdate
            ? `/wp/v2/lugares-atencion/${self.formData.ID}`
            : "/wp/v2/lugares-atencion",
          data: {
            title: this.formData.name,
            status: "publish",
            meta: this.formData.meta,
          },
        })
          .then(function (response) {
            if (response.id) {
              self.formData.ID = response.id;
              self.apbPostMeta.ID = response.id;
              self.apbPostMeta.custom_schedule.use_custom_schedule = true;
              self.apbPostMeta.custom_schedule.working_days_mode =
                "override_days";
              jQuery.ajax({
                url: wcfm_params.ajax_url,
                type: "POST",
                dataType: "json",
                data: {
                  action: "jet_apb_save_post_meta",
                  jet_apb_post_meta: self.apbPostMeta,
                  _nonce: window.jetApbPostMeta._nonce,
                },
              });
              self.$CXNotice.add({
                message: "Lugar de atención guardado",
                type: "success",
                duration: 7000,
              });
            }
          })
          .catch(function (error) {
            self.$CXNotice.add({
              message: error.message,
              type: "error",
              duration: 7000,
            });
          })
          .finally(function () {
            $("#wcfm-content").unblock();
          });
      },
    },
  });
});
