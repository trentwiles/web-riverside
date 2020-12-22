function load()
{
    $.get("analytics.php", function(data, status){
        console.log(status+" (yay!)")
        var traffic = JSON.stringify(data);
        var carray = [] // array of countries
        var harray = [] // array of hits
        var countries = traffic.data.countries.forEach(country => {
            carray.push(country)
        });
        var hits = traffic.data.hits.forEach(hit => {
            harray.push(hit)
        });
        console.log(hits)
        console.log(countries)
    });
}
