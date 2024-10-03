// Adapted from the Sun and moon rise/set project: https://web.archive.org/web/20100409090517/http://bodmas.org/astronomy/riset.html

"use strict";

// This is a translation of a set of routines from Montenbruck and Pfleger's
// Astonomy on the Computer 2nd english ed - see chapter 3.8 the sunset progrm
//

//
//  *** Functions go here - mostly adapted from Montenbruck and Pfleger section 3.8 ***
//

function frac(x) {
	//
	//	returns the fractional part of x as used in minimoon and minisun
	//
	return x % 1;
}

function range(x) {
	//
	//	returns an angle in degrees in the range 0 to 360
	//
	return ((x) - 360.0 * (Math.floor((x) / 360.0)));
}

/* function amjd(date) {
	//
	//	Takes the day, month, year and hours in the day and returns the
	//  modified julian day number defined as mjd = jd - 2400000.5
	//  checked OK for Greg era dates - 26th Dec 02
	//
	const day = date.getDate();
	let month = date.getMonth() + 1;
	let year = date.getFullYear();
	const hour = date.getHours();
	console.log(day, month, year, hour);
	let a, b;
	if (month <= 2) {
		month = month + 12;
		year = year - 1;
	}
	a = 10000.0 * year + 100.0 * month + day;
	if (a <= 15821004.1) {
		b = -2 * Math.floor((year + 4716) / 4) - 1179;
	}
	else {
		b = Math.floor(year / 400) - Math.floor(year / 100) + Math.floor(year / 4);
	}
	a = 365.0 * year - 679004.0;
	return (a + b + Math.floor(30.6001 * (month + 1)) + day + hour / 24.0);
} */

function mjd(date) {
	return (Math.round(date / (1000 * 60 * 60 * 24)) + 2440587.5) - 2400000.5;
}

function quad(ym, yz, yp) {
	//
	//	finds the parabola throuh the three points (-1,ym), (0,yz), (1, yp)
	//  and returns the coordinates of the max/min (if any) xe, ye
	//  the values of x where the parabola crosses zero (roots of the quadratic)
	//  and the number of roots (0, 1 or 2) within the interval [-1, 1]
	//
	//	well, this routine is producing sensible answers
	//
	//  results passed as array [nz, z1, z2, xe, ye]
	//
	let quadout = [];

	let nz = 0;
	const a = 0.5 * (ym + yp) - yz;
	const b = 0.5 * (yp - ym);
	const c = yz;
	const xe = -b / (2 * a);
	const ye = (a * xe + b) * xe + c;
	const dis = b * b - 4.0 * a * c;
	if (dis > 0) {
		const dx = 0.5 * Math.sqrt(dis) / Math.abs(a);
		var z1 = xe - dx;
		var z2 = xe + dx;
		if (Math.abs(z1) <= 1.0) nz += 1;
		if (Math.abs(z2) <= 1.0) nz += 1;
		if (z1 < -1.0) z1 = z2;
	}
	quadout[0] = nz;
	quadout[1] = z1;
	quadout[2] = z2;
	quadout[3] = xe;
	quadout[4] = ye;
	return quadout;
}

function lmst(mjd, glong) {
	//
	//	Takes the mjd and the longitude (west negative) and then returns
	//  the local sidereal time in hours. Im using Meeus formula 11.4
	//  instead of messing about with UTo and so on
	//
	const d = mjd - 51544.5
	const t = d / 36525.0;
	const lst = range(280.46061837 + 360.98564736629 * d + 0.000387933 * t * t - t * t * t / 38710000);
	return (lst / 15.0 + glong / 15);
}

function minisun(t) {
	//
	//	returns the ra and dec of the Sun in an array called suneq[]
	//  in decimal hours, degs referred to the equinox of date and using
	//  obliquity of the ecliptic at J2000.0 (small error for +- 100 yrs)
	//	takes t centuries since J2000.0. Claimed good to 1 arcmin
	//
	const p2 = 6.283185307, coseps = 0.91748, sineps = 0.39778;
	let suneq = [];

	const M = p2 * frac(0.993133 + 99.997361 * t);
	const DL = 6893.0 * Math.sin(M) + 72.0 * Math.sin(2 * M);
	const L = p2 * frac(0.7859453 + M / p2 + (6191.2 * t + DL) / 1296000);
	const SL = Math.sin(L);
	const X = Math.cos(L);
	const Y = coseps * SL;
	const Z = sineps * SL;
	const RHO = Math.sqrt(1 - Z * Z);
	const dec = (360.0 / p2) * Math.atan(Z / RHO);
	let ra = (48.0 / p2) * Math.atan(Y / (X + RHO));
	if (ra < 0) ra += 24;
	suneq[1] = dec;
	suneq[2] = ra;
	return suneq;
}

function minimoon(t) {
	//
	// takes t and returns the geocentric ra and dec in an array mooneq
	// claimed good to 5' (angle) in ra and 1' in dec
	// tallies with another approximate method and with ICE for a couple of dates
	//
	const p2 = 6.283185307, arc = 206264.8062, coseps = 0.91748, sineps = 0.39778;
	let mooneq = [];

	const L0 = frac(0.606433 + 1336.855225 * t);	// mean longitude of moon
	const L = p2 * frac(0.374897 + 1325.552410 * t) //mean anomaly of Moon
	const LS = p2 * frac(0.993133 + 99.997361 * t); //mean anomaly of Sun
	const D = p2 * frac(0.827361 + 1236.853086 * t); //difference in longitude of moon and sun
	const F = p2 * frac(0.259086 + 1342.227825 * t); //mean argument of latitude

	// corrections to mean longitude in arcsec
	let DL = 22640 * Math.sin(L)
	DL += -4586 * Math.sin(L - 2 * D);
	DL += +2370 * Math.sin(2 * D);
	DL += +769 * Math.sin(2 * L);
	DL += -668 * Math.sin(LS);
	DL += -412 * Math.sin(2 * F);
	DL += -212 * Math.sin(2 * L - 2 * D);
	DL += -206 * Math.sin(L + LS - 2 * D);
	DL += +192 * Math.sin(L + 2 * D);
	DL += -165 * Math.sin(LS - 2 * D);
	DL += -125 * Math.sin(D);
	DL += -110 * Math.sin(L + LS);
	DL += +148 * Math.sin(L - LS);
	DL += -55 * Math.sin(2 * F - 2 * D);

	// simplified form of the latitude terms
	const S = F + (DL + 412 * Math.sin(2 * F) + 541 * Math.sin(LS)) / arc;
	const H = F - 2 * D;
	let N = -526 * Math.sin(H);
	N += +44 * Math.sin(L + H);
	N += -31 * Math.sin(-L + H);
	N += -23 * Math.sin(LS + H);
	N += +11 * Math.sin(-LS + H);
	N += -25 * Math.sin(-2 * L + F);
	N += +21 * Math.sin(-L + F);

	// ecliptic long and lat of Moon in rads
	const L_moon = p2 * frac(L0 + DL / 1296000);
	const B_moon = (18520.0 * Math.sin(S) + N) / arc;

	// equatorial coord conversion - note fixed obliquity
	const CB = Math.cos(B_moon);
	const X = CB * Math.cos(L_moon);
	const V = CB * Math.sin(L_moon);
	const W = Math.sin(B_moon);
	const Y = coseps * V - sineps * W;
	const Z = sineps * V + coseps * W
	const RHO = Math.sqrt(1.0 - Z * Z);
	const dec = (360.0 / p2) * Math.atan(Z / RHO);
	let ra = (48.0 / p2) * Math.atan(Y / (X + RHO));
	if (ra < 0) ra += 24;
	mooneq[1] = dec;
	mooneq[2] = ra;
	return mooneq;
}

function sin_alt(iobj, mjd0, hour, glong, cglat, sglat) {
	//
	//	this rather mickey mouse function takes a lot of
	//  arguments and then returns the sine of the altitude of
	//  the object labelled by iobj. iobj = 1 is moon, iobj = 2 is sun
	//
	const rads = 0.0174532925;
	let objpos = [];
	const mjd = mjd0 + hour / 24.0;
	const t = (mjd - 51544.5) / 36525.0;
	if (iobj == 1) {
		objpos = minimoon(t);
	}
	else {
		objpos = minisun(t);
	}
	const ra = objpos[2];
	const dec = objpos[1];
	// hour angle of object
	const tau = 15.0 * (lmst(mjd, glong) - ra);
	// sin(alt) of object using the conversion formulas
	const salt = sglat * Math.sin(rads * dec) + cglat * Math.cos(rads * dec) * Math.cos(rads * tau);
	return salt;
}

function find_rise_set(iobj, mjd, tz, glong, glat) {
	let utrise, utset;
	const rads = 0.0174532925;
	let quadout = [];
	// let sinho;
	// const   always_up = " ****";
	// const always_down = " ....";
	// let outstring = "";

	const sinho = (iobj == 1) ? Math.sin(rads * 8 / 60) : Math.sin(rads * -0.833);		//moonrise taken as centre of moon at +8 arcmin
	const sglat = Math.sin(rads * glat);
	const cglat = Math.cos(rads * glat);
	const date = mjd - tz / 24;
	// console.log(date);
	let rise = false;
	let sett = false;
	let above = false;
	let hour = 1.0;
	let ym = sin_alt(iobj, date, hour - 1.0, glong, cglat, sglat) - sinho;
	if (ym > 0.0) above = true;
	while (hour < 25 && (sett == false || rise == false)) {
		const yz = sin_alt(iobj, date, hour, glong, cglat, sglat) - sinho;
		const yp = sin_alt(iobj, date, hour + 1.0, glong, cglat, sglat) - sinho;
		quadout = quad(ym, yz, yp);
		const nz = quadout[0];
		const z1 = quadout[1];
		const z2 = quadout[2];
		const xe = quadout[3];
		const ye = quadout[4];

		// case when one event is found in the interval
		if (nz == 1) {
			if (ym < 0.0) {
				utrise = hour + z1;
				rise = true;
			}
			else {
				utset = hour + z1;
				sett = true;
			}
		} // end of nz = 1 case

		// case where two events are found in this interval
		// (rare but whole reason we are not using simple iteration)
		if (nz == 2) {
			if (ye < 0.0) {
				utrise = hour + z2;
				utset = hour + z1;
			}
			else {
				utrise = hour + z1;
				utset = hour + z2;
			}
		}

		// set up the next search interval
		ym = yp;
		hour += 2.0;

	} // end of while loop

	/* if (rise == true || sett == true ) {
		if (rise == true) outstring += " " + hrsmin(utrise);
		else outstring += " ----";
		if (sett == true) outstring += "<br>" + hrsmin(utset);
		else outstring += " ----";
		}
	else {
		if (above == true) outstring += always_up + "<br>" + always_up;
		else outstring += always_down + "<br>" + always_down;
		} */

	return { 'rise': utrise, 'set': utset };
}

