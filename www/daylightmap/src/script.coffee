class DaylightMap
    constructor: (svg, date, options = {}) ->
        unless SunCalc? and $? and d3?
            throw new Error "Unmet dependency (requires d3.js, jQuery, SunCalc)"
        unless svg
            throw new TypeError "DaylightMap must be instantiated with a valid SVG"

        @options =
            tickDur: options.tickDur || 400
            shadowOpacity: options.shadowOpacity || 0.16
            bgColorLeft: options.bgColorLeft || '#42448A'
            bgColorRight: options.bgColorRight || '#376281'
            lightsColor: options.lightsColor || '#FFBEA0'
            lightsOpacity: options.lightsOpacity || 0.5
            sunOpacity: options.sunOpacity || 0.11

        @PRECISION_LAT = 1 # How many latitudinal degrees per point when checking solar position.
        @PRECISION_LNG = 10 # How may longitudial degrees per sunrise / sunset path point.
        @MAP_WIDTH = options.width || 1100
        @MAP_HEIGHT = (@MAP_WIDTH / 2)
        @SCALAR_X = (@MAP_WIDTH / 360)
        @SCALAR_Y = (@MAP_HEIGHT / 180)
        @PROJECTION_SCALE = (@MAP_WIDTH / 6.25)
        @WORLD_PATHS_URL = 'https://s3-us-west-2.amazonaws.com/s.cdpn.io/215059/world-110m.json'
        @CITIES_DATA_URL = 'https://s3-us-west-2.amazonaws.com/s.cdpn.io/215059/cities-200000.json'

        @svg = svg
        @isAnimating = false
        @cities = []
        @animInterval = null
        @currDate = date || new Date()

    colorLuminance: (hex, lum = 0) ->
        c = null
        i = 0
        rgb = '#'
        hex = String(hex).replace(/[^0-9a-f]/gi, '')
        if hex.length < 6
            hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2]
        while i < 3
            c = parseInt(hex.substr(i * 2, 2), 16)
            c = Math.round(Math.min(Math.max(0, c + c * lum), 255)).toString(16)
            rgb += ('00' + c).substr(c.length)
            i++
        return rgb

    isDaylight: (obj) ->
        return obj.altitude > 0

    isNorthSun: ->
        return @isDaylight(SunCalc.getPosition(@currDate, 90, 0))

    getSunriseSunsetLatitude: (lng, northSun) ->
        if northSun
            startLat = -90
            endLat = 90
            delta = @PRECISION_LAT
        else
            startLat = 90
            endLat = -90
            delta = -(@PRECISION_LAT)

        lat = startLat
        while lat isnt endLat
            if @isDaylight(SunCalc.getPosition(@currDate, lat, lng))
                return lat
            lat += delta
        return lat

    getAllSunPositionsAtLng: (lng) ->
        lat = -90
        peak = 0
        result = []
        while lat < 90
            alt = SunCalc.getPosition(@currDate, lat, lng).altitude
            if alt > peak
                peak = alt
                result = [peak, lat]
            lat += @PRECISION_LNG
        return result

    getSunPosition: ->
        lng = -180
        coords = []
        peak = 0
        while lng < 180
            alt = @getAllSunPositionsAtLng(lng)
            if alt[0] > peak
                peak = alt[0]
                result = [alt[1], lng]
            lng += @PRECISION_LAT
        return @coordToXY(result)

    getAllSunriseSunsetCoords: (northSun) ->
        lng = -180
        coords = []
        while lng < 180
            coords.push([@getSunriseSunsetLatitude(lng, northSun), lng]);
            lng += @PRECISION_LNG

        # Add the last point to the map.
        coords.push([@getSunriseSunsetLatitude(180, northSun), 180])

        return coords

    lineFunction: d3.svg.line().x((d) -> d.x).y((d) -> d.y ).interpolate('basis')

    coordToXY: (coord) ->
        x = (coord[1] + 180) * @SCALAR_X
        y = @MAP_HEIGHT - (coord[0] + 90) * @SCALAR_Y
        return { x: x, y: y }

    getCityOpacity: (coord) ->
        if SunCalc.getPosition(@currDate, coord[0], coord[1]).altitude > 0
            return 0
        return 1

    getCityRadius: (population) ->
        if population < 200000
            return 0.3
        else if population < 500000
            return 0.4
        else if population < 100000
            return 0.5
        else if population < 2000000
            return 0.6
        else if population < 4000000
            return 0.8
        else
            return 1

    getPath: (northSun) ->
        path = []
        coords = @getAllSunriseSunsetCoords(northSun)
        coords.forEach (val) =>
            path.push(@coordToXY(val))
        return path

    getPathString: (northSun) ->
        unless northSun then yStart = 0 else yStart = @MAP_HEIGHT
        pathStr = "M 0 #{yStart}"
        path = @getPath(northSun)
        pathStr += @lineFunction(path)

        # Close the path back to the origin.
        pathStr += " L #{@MAP_WIDTH}, #{yStart} "
        pathStr += " L 0, #{yStart} "
        return pathStr

    createDefs: ->
        d3.select(@svg)
            .append('defs')
            .append('linearGradient')
            .attr('id', 'gradient')
            .attr('x1', '0%')
            .attr('y1', '0%')
            .attr('x2', '100%')
            .attr('y2', '0%')

        d3.select('#gradient')
            .append('stop')
            .attr('offset', '0%')
            .attr('stop-color', @options.bgColorLeft)

        d3.select('#gradient')
            .append('stop')
            .attr('offset', '100%')
            .attr('stop-color', @options.bgColorRight)

        d3.select(@svg)
            .select('defs')
            .append('linearGradient')
            .attr('id', 'landGradient')
            .attr('x1', '0%')
            .attr('y1', '0%')
            .attr('x2', '100%')
            .attr('y2', '0%')

        d3.select('#landGradient')
            .append('stop')
            .attr('offset', '0%')
            .attr('stop-color', @colorLuminance(@options.bgColorLeft, -0.2))

        d3.select('#landGradient')
            .append('stop')
            .attr('offset', '100%')
            .attr('stop-color', @colorLuminance(@options.bgColorRight, -0.2))

        d3.select(@svg)
            .select('defs')
            .append('radialGradient')
            .attr('id', 'radialGradient')

        d3.select('#radialGradient')
            .append('stop')
            .attr('offset', '0%')
            .attr('stop-opacity', @options.sunOpacity)
            .attr('stop-color', "rgb(255, 255, 255)")

        d3.select('#radialGradient')
            .append('stop')
            .attr('offset', '100%')
            .attr('stop-opacity', 0)
            .attr('stop-color', 'rgb(255, 255, 255)')

    drawSVG: ->
        d3.select(@svg)
            .attr('width', @MAP_WIDTH)
            .attr('height', @MAP_HEIGHT)
            .attr('viewBox', "0 0 #{@MAP_WIDTH} #{@MAP_HEIGHT}")
            .append('rect')
            .attr('width', @MAP_WIDTH)
            .attr('height', @MAP_HEIGHT)
            .attr('fill', "url(#gradient)")

    drawSun: ->
        xy = @getSunPosition()
        d3.select(@svg)
            .append('circle')
            .attr('cx', xy.x)
            .attr('cy', xy.y)
            .attr('id', 'sun')
            .attr('r', 150)
            .attr('opacity', 1)
            .attr('fill', 'url(#radialGradient)')

    drawPath: ->
        path = @getPathString(@isNorthSun())
        d3.select(@svg)
            .append('path')
            .attr('id', 'nightPath')
            .attr('fill', "rgb(0,0,0)")
            .attr('fill-opacity', @options.shadowOpacity)
            .attr('d', path)

    drawLand: ->
        $.get @WORLD_PATHS_URL, (data) =>
            projection = d3.geo.equirectangular()
                .scale(@PROJECTION_SCALE)
                .translate([@MAP_WIDTH / 2, @MAP_HEIGHT /2])
                .precision(0.1)

            worldPath = d3.geo.path().projection(projection)

            d3.select(@svg)
                .append('path')
                .attr('id', 'land')
                .attr('fill', 'url(#landGradient)')
                .datum(topojson.feature(data, data.objects.land))
                .attr('d', worldPath)

            # Asynchronous so re-order the elements here.
            @shuffleElements()


    drawCities: ->
        $.get @CITIES_DATA_URL,  (data) =>
            data.forEach (val, i) =>
                coords = [parseFloat(val[2]), parseFloat(val[3])]
                xy = @coordToXY(coords)
                id = "city#{i}"
                opacity = @getCityOpacity(coords)
                radius = @getCityRadius(val[0])

                d3.select(@svg)
                    .append('circle')
                    .attr('cx', xy.x)
                    .attr('cy', xy.y)
                    .attr('id', id)
                    .attr('r', radius)
                    .attr('opacity', opacity * @options.lightsOpacity)
                    .attr('fill', @options.lightsColor)

                @cities.push
                    title: val[1]
                    country: val[5]
                    latlng: coords
                    xy: xy
                    population: parseInt(val[0])
                    id: id
                    opacity: opacity

    searchCities: (str) ->
        cities = _.filter(@cities, (val) -> (val.title.toLowerCase().indexOf(str) is 0))
        cities = _.sortBy(cities, (val) -> val.population)
        cities.reverse()

    redrawSun: (animate) ->
        xy = @getSunPosition()
        curX = parseInt(d3.select("#sun").attr('cx'))

        if animate and ((Math.abs(xy.x - curX)) < (@MAP_WIDTH * 0.8))

            d3.select("#sun")
                .transition()
                .duration(@options.tickDur)
                .ease('linear')
                .attr('cx', xy.x)
                .attr('cy', xy.y)
        else
            d3.select("#sun")
                .attr('cx', xy.x)
                .attr('cy', xy.y)

    redrawCities: ->
        k = 0
        @cities.forEach (val, i) =>
            opacity = @getCityOpacity(val.latlng)
            if val.opacity isnt opacity
                @cities[i].opacity = opacity
                k++
                d3.select("##{val.id}")
                    .transition()
                    .duration(@options.tickDur * 2)
                    .attr('opacity', @options.lightsOpacity * opacity)

    redrawPath: (animate) ->
        path = @getPathString(@isNorthSun(@currDate))
        nightPath = d3.select('#nightPath')
        if animate
            nightPath.transition()
                .duration(@options.tickDur)
                .ease('linear')
                .attr('d', path)
        else
            nightPath.attr('d', path)

    redrawAll: (increment = 15, animate = true) ->
        @currDate.setMinutes(@currDate.getMinutes() + increment)
        @redrawPath(animate)
        @redrawSun(animate)
        @redrawCities()

    drawAll: ->
        @drawSVG()
        @createDefs()
        @drawLand()
        @drawPath()
        @drawSun()
        @drawCities()

    shuffleElements: ->
        $('#land').insertBefore('#nightPath')
        $('#sun').insertBefore('#land')

    animate: (increment = 0) ->
        unless @isAnimating
            @isAnimating = true
            @animInterval = setInterval =>
                @redrawAll(increment)
                $(document).trigger('update-date-time', @currDate)
            , @options.tickDur

    stop: ->
        @isAnimating = false
        clearInterval @animInterval

    init: ->
        @drawAll()
        setInterval =>
            return if @isAnimating
            @redrawAll(1, false)
            $(document).trigger('update-date-time', @currDate)
        , 60000


updateDateTime = (date) ->
    # tz = date.toString().match(/\(([A-Za-z\s].*)\)/)[1]
    $('.curr-time').find('span').html(moment(date).format("HH:mm"))
    $('.curr-date').find('span').text(moment(date).format("DD MMM"))


$(document).ready ->
    svg = document.getElementById('daylight-map')
    map = new DaylightMap(svg, new Date())
    map.init()

    updateDateTime(map.currDate)

    $(document).on 'update-date-time', (date) ->
        updateDateTime(map.currDate)

    $('.toggle-btn').on 'click', (e) ->
        e.preventDefault()
        $el = $(this)
        $el.toggleClass 'active'

    $('.js-skip').on 'click', (e) ->
        e.preventDefault()
        $el = $(this)
        animate = false
        map.stop()
        $('.js-animate').removeClass 'animating'

        if $el.attr('data-animate') then animate = true
        map.redrawAll(parseInt($(this).attr('data-skip')), animate)
        updateDateTime(map.currDate)

    $('.js-animate').on 'click', (e) ->
        $el = $(this)
        e.preventDefault()
        if $el.hasClass 'animating'
            $el.removeClass 'animating'
            map.stop()
        else
            $el.addClass 'animating'
            map.animate(10)
