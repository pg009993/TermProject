// waits until the document is ready
$(document).ready(function() {
    // gets elements from the page
    var $selector = $('#selector');
    var $form = $('#form');
    var $submit = $('#submit');
    var $container = $('#container');
    var $title = $('#title');
    var container = $container[0];
    var data, map;

    // sends initial request for the list
    $.ajax({
        url: 'data.php',
        type: 'GET',
        data: {requesting: 'list'},
        success: function(response) {
            // adds each value in the response as an option tag in the dropdown
            var list = response.split(',');
            list.forEach(function(item) {
                $selector.append('<option>' + item + '</option>');
            });

            // enables the form and adds the handlers
            $submit.prop('disabled', false);
            $form.on('submit', formSubmit);
            
            // adds a listener to draw the map on resize to ensure responsiveness
            $(window).on('resize', drawMap);
            
            // draws the map
            drawMap();
        },
        error: function(error) {
            alert('Error loading dataset list');
            console.log(error);
        }
    });

    // draws the map
    function drawMap() {
        // erases the previous map, and sets the height of the container to ensure the height/width ratio is preserved
        $container.empty();
        $container.height($container.width() * .53);
        
        // if the data has been loaded, set the title of the map to the loaded data title and draw the map with that data
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
        } else { // if the data hasn't been loaded, draw a blank map and set the title to 'Select a Dataset'
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

    // handles form submit, requests dataset that was selected in dropdown
    function formSubmit(e) {
        e.preventDefault();

        $.ajax({
            url: 'data.php',
            type: 'GET',
            data: {requesting: 'data', dataset: $selector.val()},
            success: function(response) {
                // parses the response and draws the map
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