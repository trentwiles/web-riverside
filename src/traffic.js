function load()
{
    $.get("analytics.php", function(data, status){
        console.log(status)
        var traffic = JSON.stringify(data);
        var carray = [] // array of countries
        var harray = [] // array of hits
        var countries = data.data.countries.forEach(country => {
            carray.push(country)
        });
        var hits = data.data.hits.forEach(hit => {
            harray.push(hit)
        });
        console.log(hits)
        console.log(countries)
    });
}
