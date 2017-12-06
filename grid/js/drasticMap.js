/* DrasticTools version 0.6.23
 * DrasticTools is released under the GPL license: 
 * Copyright (C) 2007 email: info@drasticdata.nl
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * =========================================================================================
 * If you find this sofware useful, we appreciate your donation on http://www.drasticdata.nl
 * Suggestions for improvement can be sent to: info@drasticdata.nl
 * ========================================================================================= 
 */
var dsmap = {
"MSGUPDATEFAILED": "Failed to update data",
"MSGREFRESHFAILED": "Failed to refresh map"
};
var drasticMap = new Class ({
    options: {
		path: location.pathname,		// path of the php file to call		
		pathimg: "DrasticTools/img/",	// path to images
		addparams: "",					// optional parameter string to pass to PHP GET requests, use "&var1=value1&var2=value2"		

		coordcol: -1,					// the column having the coordinates			
		displaycol: -1,					// variable to be displayed in bars or circles or red/green markers
		icon: null,						// icon to use for marker; "bar", "circle" or "boolean". Defaults to the default Google marker
		markercolumns: null,			// Array of columns to be shown in the markerwindow. Defaults to all.
		
		onClick: function(id){this.DefaultOnClick(id);},
		onMouseOver: function(id){this.DefaultOnMouseOver(id);}		
    },
	initialize: function(container, options){
		var t = this;
		t.container 	= $(container) || alert('container '+container+' not found...');
		t.setOptions(options);
		t.getMetaData();
		
		// gifs:
		t.gifloading= t.options.pathimg+'loading.gif';		
		t.ddPowered = t.options.pathimg+'DDPowered.png';
		t.ddPowered2 = t.options.pathimg+'DDPowered2.png';
		
		// Define settings of columns
		t.columns = [];
		if (t.options.columns) {
			//fill in configuration setting from t.options.columns
			for (var i=0, j=0; i<t.options.columns.length; i++, j++){
				if ($defined(t.options.columns[i]) && $defined(t.options.columns[i].name) && !t.cols.contains(t.options.columns[i].name)) {alert("no field with name '"+t.options.columns[i].name+"' in data source"); continue;}
				t.columns[j] = {};
				if ($defined(t.options.columns[i].name)) t.columns[j].name = t.options.columns[i].name;
				if ($defined(t.options.columns[i].displayname)) t.columns[j].displayname = t.options.columns[i].displayname;
			}
		}
		else {
			for (var i=0; i<t.cols.length; i++){
				t.columns[i] = {};
				t.columns[i].name = t.cols[i];		
			}			
		}
		// fill in missing fields
		for (var i=0; i<t.columns.length; i++){
			if (!$defined(t.columns[i].displayname)) {
				t.columns[i].displayname = t.columns[i].name;
			}
		}
		// Set column to be displayed in bars
		if (t.options.displaycol == -1) {
			for (var i=0; i<t.cols_numeric.length; i++){
				if (t.cols_numeric[i] == t.idname) continue;
				t.options.displaycol = t.cols_numeric[i];
				break;
			}
			if (t.options.displaycol == -1) t.options.displaycol = 0;
		}
		// Set column to be used as coordinates
		if (t.options.coordcol == -1) {
			// get first 5 rows:
			myajax = new Request({
				url: t.options.path+'?op=v&start=0&end=5'+t.options.addparams,
				async: false,
				method: 'get',
				onFailure: function(){alert(dsmap["MSGREFRESHFAILED"]);},
				onSuccess : function(responseText){
					t.data5row = JSON.decode(responseText);
				}
			}).send();
			var arr = t.data5row[1];
			for(var i=0; i < Math.min(5, t.num_rows); i++)  {
				for(var j=0; j < t.num_fields; j++)  {
					if (arr[i][j].match(/[+-]?[0-9]*\.?[0-9]*,[+-]?[0-9]*\.?[0-9]*/)) {
						t.options.coordcol = t.cols[j];
						break;
					}
				}
				if (t.options.coordcol != -1) break;
			}
			if (t.options.coordcol == -1) return;
		}

		t.marker = new Array;
		t.map = new GMap2($(t.container));
		t.map.setCenter(new GLatLng(0,0),0);
		//t.map.addControl(new GSmallMapControl());
		t.map.addControl(new GLargeMapControl());
		t.map.addControl(new GOverviewMapControl());
		t.map.addControl(new GMapTypeControl());
		t.map.enableScrollWheelZoom();
		
		//t.mm = new GMarkerManager(t.map, {trackMarkers: true});
		// Using the open source markermanager:
		t.mm = new MarkerManager(t.map, {trackMarkers: true});
				
		//Load indicator:
		t.loading = new Element('div', {'styles': {'position': 'relative', 'z-index': '100'}}).inject(t.container);
		var img = new Element('img', {'src': t.gifloading}).inject(t.loading);
		var c = img.getCoordinates();
		t.loading.setStyles({'width': c.width, 'height': c.height});						

		t.refresh();
	},
	getMetaData: function() {
		var t = this;
		var myajax = new Request({
			url: t.options.path+'?op=vm'+t.options.addparams,
			async: false,
			method: 'get',
			onFailure: function(){alert(dsgrid["MSGREFRESHFAILED"]);},
			onSuccess : function(responseText){
				t.metadata = JSON.decode(responseText);
				t.num_rows			= t.metadata[0];
				t.num_fields		= t.metadata[1];
				t.idname			= t.metadata[2];
				t.idcolnr			= t.metadata[3];
				t.cols				= t.metadata[4];
				t.cols_numeric		= t.metadata[5];
				t.add_allowed		= t.metadata[6];
				t.delete_allowed	= t.metadata[7];
				t.editablecols		= t.metadata[8];
				t.defaultcols		= t.metadata[9];
				if (!t.options.sortcol) t.options.sortcol = t.metadata[10];
				if (!t.options.sort) t.options.sort = t.metadata[11];
				t.flds				= new Hash(t.metadata[12]);				
			}
		}).send();
	},
	showLoading: function() {
		var t = this;
		var c = t.container.getCoordinates();
		t.loading.setStyles({
			'visibility': 'visible',
			'top': (c.height/2) - 40,
			'left': (c.width/2) - 16
		    });
	},	
	hideLoading: function() {
		var t = this;
		t.loading.setStyles({'visibility': 'hidden'});
	},	
	UpdateCell:	function(id, col, value) {
		var t = this;
		var url = t.options.path+'?op=u&id='+id+'&col='+col+'&value='+escape(value)+t.options.addparams;
		t.showLoading();
		myajax = new Request({
			url: url,
			method: 'get',
			onFailure: function(){alert(t.dsmap["MSGUPDATEFAILED"]);},
			onComplete: function(responseText){
				if(responseText[0]=='0')
					alert(t.dsmap["MSGUPDATEFAILED"]);
				t.hideLoading();
			}
		}).send();
	},
	// Refresh the whole map
	refresh: function () {
		var t = this;
		var url = t.options.path+'?op=v&cols='+t.idname+','+t.options.coordcol+','+t.options.displaycol+t.options.addparams;
		t.showLoading();
		GDownloadUrl(url, function(data, responseCode) {
			t.data = JSON.decode(data);
			var obj = t.data[1];
			var b = new GLatLngBounds();
			if (t.marker) {
				t.mm.clearMarkers();
				delete t.marker;
				t.marker = new Array;				
			}
			// Put markers on the map:
			for (var i=0; i<obj.length; i++) {
				if (obj[i][1] == null) continue;
				var point = GLatLng.fromUrlValue(obj[i][1]);
				b.extend(point);
				var icon;
				if (!t.options.icon) {
					icon = new GIcon(G_DEFAULT_ICON);
					icon.infoWindowAnchor = new GPoint(10, 11);
				}
				else {
					icon = new GIcon();
					if (t.options.icon == "boolean") {
						icon = new GIcon(G_DEFAULT_ICON);
						icon.infoWindowAnchor = new GPoint(10, 11);						
						if (obj[i][2] == 0) {
							icon.image = "http://maps.google.com/mapfiles/ms/micons/red-dot.png";
						} else {
							icon.image = "http://maps.google.com/mapfiles/ms/micons/green-dot.png";
						}
						icon.iconSize = new GSize(32, 32);
						icon.iconAnchor = new GPoint(16, 32);
					}
					if (t.options.icon == "bar") {
						var w = 10;
						var h = 20;
						icon.image = t.options.path+"?op=vb&id="+obj[i][0]+"&colname="+t.options.displaycol+"&w="+w+"&h="+h+t.options.addparams;
						icon.iconSize = new GSize(w, h);
						icon.iconAnchor = new GPoint(w/2, h);
						icon.infoWindowAnchor = new GPoint(w/2, h);			
					}
					if (t.options.icon == "circle") {
						var w = 20;
						icon.image = t.options.path+"?op=vc&id="+obj[i][0]+"&colname="+t.options.displaycol+"&w="+w+t.options.addparams;
						icon.iconSize = new GSize(w, w);
						icon.iconAnchor = new GPoint(w/2, w/2);
						icon.infoWindowAnchor = new GPoint(w/2, w/2);					
					}
				}
				t.marker[i] = new GMarker(point, {icon: icon, draggable: t.editablecols.contains(t.options.coordcol)});					
				t.marker[i].id = obj[i][0];
				GEvent.addListener(t.marker[i], "click", function() {
					t.fireEvent("onClick", this.id);
				});
				GEvent.addListener(t.marker[i], "dragstart", function() {
					this.closeInfoWindow();
				});
				GEvent.addListener(t.marker[i], "dragend", function() {
					t.UpdateCell(this.id, t.options.coordcol, this.getPoint().toUrlValue());
				});
			}	
			t.map.setCenter(b.getCenter(), t.map.getBoundsZoomLevel(b));
			t.mm.addMarkers(t.marker, 2);
			t.mm.refresh();			
			t.hideLoading();
		});
	},
	DefaultOnClick:function(id) {
		var t = this;
		var cols = $defined(t.options.markercolumns)? t.options.markercolumns: t.cols;
		var url = t.options.path+'?op=v&id='+id+'&cols='+cols+t.options.addparams;
		t.showLoading();
		myajax = new Request({
			url: url,
			method: 'get',
			onFailure: function(){alert(t.dsmap["MSGREFRESHFAILED"]);},
			onComplete: function(responseText){
				var data = JSON.decode(responseText);
				var row = data[1];
				var str = '';
				for (var i=0; i<t.columns.length; i++){
					// Find data for name:
					for (var j=0; j<cols.length; j++) if (cols[j] == t.columns[i].name) break;
					var dataForName;
					if (j<cols.length) dataForName = row[0][j]; else dataForName  = "";
					str += "<tr><td><b>"+t.columns[i].displayname+"</b>: </td><td>"+dataForName+"</td></tr>";
				}
				/* OLD
				for (var i=0; i<cols.length; i++) {
					str += "<tr><td><b>"+cols[i]+"</b>: </td><td>"+((row[0][i])?row[0][i]:"")+"</td></tr>";
				}*/
				var j;
				for (j=0; j< t.data[1].length; j++) {if (t.marker[j].id == id) break;}
				t.marker[j].openInfoWindowHtml("<table style=\"font: 10px arial;\">"+str+"</table>");	
				t.hideLoading();
			}
		}).send();
	}
});
drasticMap.implement(new Options, new Events);