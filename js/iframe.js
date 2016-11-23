$(document).ready(function() {
    var $selector = $('#selector');
    var $form = $('#form');
    var $submit = $('#submit');
    var $container = $('#container');
    var $title = $('#title');
    var container = $container[0];
    var data, map;

    $.ajax({
        url: 'data.php',
        type: 'GET',
        data: {requesting: 'list'},
        success: function(response) {
            var list = response.split(',');
            list.forEach(function(item) {
                $selector.append('<option>' + item + '</option>');
            });

            $submit.prop('disabled', false);
            $form.on('submit', formSubmit);
            $(window).on('resize', drawMap);
            drawMap();
        },
        error: function(error) {
            alert('Error loading dataset list');
            console.log(error);
        }
    });

    function drawMap() {
        $container.empty();
        $container.height($container.width() * .53);
        
        if (data) {
            $title.text($selector.val());
            
            map = new Datamap({
                element: container,
                scope: 'usa',
                data: data,
                fills: {
                    defaultFill: 'rgb(20, 20, 20)'
                },
                geographyConfig: {
                    popupTemplate: function(geo, data) {
                        return '<div class="hoverinfo"><strong>' + geo.properties.name + ': ' + (data && data.data ? data.data : 'No Data') + '</strong></div>';
                    }
                }
            });
        } else {
            $title.text('Select a Dataset');
            
            map = new Datamap({
                element: container,
                scope: 'usa',
                fills: {
                    defaultFill: 'rgba(40, 40, 40, .5)'
                }
            });
        }
    }

    function formSubmit(e) {
        e.preventDefault();

        $.ajax({
            url: 'data.php',
            type: 'GET',
            data: {requesting: 'data', dataset: $selector.val()},
            success: function(response) {
                try {
                    data = JSON.parse(response);

                } catch (e) {
                    alert('Invalid dataset');
                    console.log(e);
                }
                drawMap();
            },
            error: function(error) {
                alert('Error loading dataset');
                console.log(error);
            }
        });

    }
});