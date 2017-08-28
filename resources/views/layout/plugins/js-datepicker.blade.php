<script src="{{ asset('js/daterangepicker.min.js')}}">  </script>
<script type="text/javascript">
$(document).ready(function() {
    $('input[name="fecha_evento"]').daterangepicker({
        locale: {
            format: 'YYYY-MM-DD'
        }
    })

    $('.fecha_evento_single').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        alwaysShowCalendars: true,
        autoUpdateInput: false,
        opens: "right",
        locale: {
            format: 'YYYY-MM-DD - YYYY-MM-DD'
        }
    })

    $('.fecha_evento_single').on('apply.daterangepicker', function (ev, picker) {
        let startDate = picker.startDate;
        $('.fecha_evento_single').val(startDate.format('YYYY-MM-DD - YYYY-MM-DD'));
    })

    $('input[name="birthdate"]').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        alwaysShowCalendars: true,
        autoUpdateInput: false,
        opens: "right",
        locale: {
            format: 'YYYY-MM-DD'
        }
    })

    $('input[name="birthdate"]').on('apply.daterangepicker', function (ev, picker) {
        let startDate = picker.startDate;
        $('input[name="birthdate"]').val(startDate.format('YYYY-MM-DD'));
        vmProfile.birthdate = startDate.format('YYYY-MM-DD')
    })
})
</script>
