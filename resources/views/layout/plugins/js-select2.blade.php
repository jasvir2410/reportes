<script src="{{ asset('js/select2.min.js')}}">  </script>
<script type="text/javascript">
    $(document).ready(function() {
        $("#select_Users").select2({
            tags: true,
            tokenSeparators: [','],
            ajax: {
                url: 'agents_queue/users',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        name: params.term
                    }
                },
                processResults: function (data, page) {
                    return {
                        results: data
                    };
                },
            }
        })
    } )
</script>
