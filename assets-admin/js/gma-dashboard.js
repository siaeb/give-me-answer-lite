jQuery( document ).ready( function( $ ) {
    
    function displayQuestionsChart() {
        var statistics         = gma_dashboard.questions;
        var answeredPercent    = Math.ceil( (statistics.answered / statistics.total) * 100 );
        var unansweredPercent  = Math.ceil( (statistics.unanswered / statistics.total) * 100 );
        var bestAnswerPercent  = Math.ceil( (statistics.best_answer / statistics.total) * 100 );

        var ctx = document.getElementById('ctx_questions_stat');
        var myChart = new Chart(ctx,
            {
                type: 'pie',
                data: {
                    datasets: [
                        {
                            data: [
                                answeredPercent,
                                unansweredPercent,
                            ],
                            backgroundColor: [
                                '#03f600',
                                '#ffa500',
                            ],
                            label: 'Dataset 1'
                        }],
                    labels: [
                        'Answered',
                        'Unanswered',
                    ]
                },
                options: {
                    responsive: false,
                },
            }
        );
    }
    
    function displayQuestionsStatByMonth() {
        var ctx = document.getElementById('ctx_questions_stat_by_month');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: gma_dashboard.questions.statistics.map( function(value) { return value.monthname; } ),
                datasets: [{
                    data: gma_dashboard.questions.statistics.map( function(value) { return value.count; } ),
                    borderColor: 'rgb(255, 99, 132)',
                    fill: 'rgb(255, 255, 255)',
                    label: 'Number of questions',
                }],
            },
            options: {
                responsive          : true,
                maintainAspectRatio: false,
                animation           : {
                    easing: 'easeInCubic',
                },
            },
        });
    }

    displayQuestionsChart();
    displayQuestionsStatByMonth();


    console.warn( gma_dashboard );


} );