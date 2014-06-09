var circles = document.querySelectorAll('.circle');
console.log(circles);
for(var i = 0; i < circles.length; i++)
{
    var circleValue = circles[i].attributes.percentage.value;
    console.log(circleValue);
    var colors;
    if(circleValue < 10)
        colors = ['#EEE', '#F00'];
    else if(circleValue >= 10 && circleValue < 50)
        colors = ['#EEE', '#FF8500'];
    else if(circleValue >= 50 && circleValue < 75)
        colors = ['#EEE', '#CCF600'];
    else if(circleValue >= 75)
        colors = ['#EEE', '#0C0'];

    var myCircle = Circles.create({
        id:         circles[i].id,
        radius:     70,
        value:      circleValue,
        maxValue:   100,
        width:      15,
        text:       function(value){return value + '%';},
        colors:     colors,
        duration:   400,
        wrpClass:   'circles-wrp',
        textClass:  'circles-text'
    });
}