!/**
 * Highcharts JS v11.4.3 (2024-05-22)
 *
 * (c) 2009-2024 Torstein Honsi
 *
 * License: www.highcharts.com/license
 */function(o) {
"object"
	==typeof module&&module.exports?(o.default=o,module.exports=o): "function"==typeof define&&define.amd?define("highcharts/themes/brand-dark",["highcharts"],function(e){return o(e),o.Highcharts=e,o;
}):o("undefined"!=typeof Highcharts?Highcharts:void 0);
}(function(o) {
"use strict"
;var e=o?o._modules: {
};

function t(o,e,t,l) {
o.hasOwnProperty(e)||(o[e]=l.apply(null,t),"function"==typeof CustomEvent&&window.dispatchEvent(new CustomEvent("HighchartsModuleLoaded",{detail: {path:e,module:o[e];
}})))}

t(e,"Extensions/Themes/BrandDark.js",[e["Core/Defaults.js"],e["Core/Utilities.js"]],function(o,e) {
var t,l;let{setOptions: r;
}=o, {
createElement: i;
}=e;return(l=t||(t= {
})).options= {
colors: ["#8087E8","#A3EDBA","#F19E53","#6699A1","#E1D369","#87B4E7","#DA6D85","#BBBAC5"],chart:{backgroundColor:{linearGradient:{x1:0,y1:0,x2:0,y2:1;
},stops:[[0,"#1f1836"],[1,"#45445d"]];
},style: {
fontFamily: "IBM Plex Sans, sans-serif";
}},title: {
style: {fontSize:"22px",fontWeight:"500",color:"#fff";
}},subtitle: {
style: {fontSize:"16px",fontWeight:"400",color:"#fff";
}},credits: {
style: {color:"#f0f0f0";
}},caption: {
style: {color:"#f0f0f0";
}},tooltip: {
borderWidth: 0,backgroundColor:"#f0f0f0",shadow:!0;
},legend: {
backgroundColor: "transparent",itemStyle:{fontWeight:"400",fontSize:"12px",color:"#000";
},itemHoverStyle: {
fontWeight: "700",color:"#fff";
}},plotOptions: {
series: {dataLabels:{color:"#46465C",style:{fontSize:"13px";
}},marker: {
lineColor: "#333";
}},boxplot: {
fillColor: "#505053";
},candlestick: {
lineColor: null,upColor:"#DA6D85",upLineColor:"#DA6D85";
},errorbar: {
color: "white";
},dumbbell: {
lowColor: "#f0f0f0";
},map: {
borderColor: "#909090",nullColor:"#78758C";
}},drilldown: {
activeAxisLabelStyle: {color:"#F0F0F3";
},activeDataLabelStyle: {
color: "#F0F0F3";
},drillUpButton: {
theme: {fill:"#fff";
}}},xAxis: {
gridLineColor: "#707073",labels:{style:{color:"#fff",fontSize:"12px";
}},lineColor:"#707073",minorGridLineColor:"#505053",tickColor:"#707073",title: {
style: {color:"#fff";
}}},yAxis: {
gridLineColor: "#707073",labels:{style:{color:"#fff",fontSize:"12px";
}},lineColor:"#707073",minorGridLineColor:"#505053",tickColor:"#707073",tickWidth:1,title: {
style: {color:"#fff",fontWeight:"300";
}}},colorAxis: {
gridLineColor: "#45445d",labels:{style:{color:"#fff",fontSize:"12px";
}},minColor:"#342f95",maxColor:"#2caffe",tickColor:"#45445d";
},mapNavigation: {
enabled: !0,buttonOptions:{theme:{fill:"#46465C","stroke-width":1,stroke:"#BBBAC5",r:2,style:{color:"#fff";
},states: {
hover: {fill:"#000","stroke-width":1,stroke:"#f0f0f0",style:{color:"#fff";
}},select: {
fill: "#000","stroke-width":1,stroke:"#f0f0f0",style:{color:"#fff";
}}}}}},rangeSelector: {
buttonTheme: {fill:"#46465C",stroke:"#BBBAC5","stroke-width":1,style:{color:"#fff";
},states: {
hover: {fill:"#1f1836",style:{color:"#fff";
},"stroke-width":1,stroke:"white";
},select: {
fill: "#1f1836",style:{color:"#fff";
},"stroke-width":1,stroke:"white";
}}},inputBoxBorderColor:"#BBBAC5",inputStyle: {
backgroundColor: "#2F2B38",color:"#fff";
},labelStyle: {
color: "#fff";
}},navigator: {
handles: {backgroundColor:"#BBBAC5",borderColor:"#2F2B38";
},outlineColor:"#CCC",maskFill:"rgba(255,255,255,0.1)",series: {
color: "#A3EDBA",lineColor:"#A3EDBA";
},xAxis: {
gridLineColor: "#505053";
}},scrollbar: {
barBackgroundColor: "#BBBAC5",barBorderColor:"#808083",buttonArrowColor:"#2F2B38",buttonBackgroundColor:"#BBBAC5",buttonBorderColor:"#2F2B38",rifleColor:"#2F2B38",trackBackgroundColor:"#78758C",trackBorderColor:"#2F2B38";
}},l.apply=function() {
i("link",{href: "https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@200;300;400;600;700",rel:"stylesheet",type:"text/css";
},null,document.getElementsByTagName("head")[0]),r(l.options);
},t;
}),t(e,"masters/themes/brand-dark.src.js",[e["Core/Globals.js"],e["Extensions/Themes/BrandDark.js"]],function(o,e) {
return o.theme=e.options,e.apply(),o;
})});