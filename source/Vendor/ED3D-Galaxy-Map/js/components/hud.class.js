var HUD = {

  'container' : null,

  /**
   *
   */
  'init' : function() {

    this.initHudAction();
    this.initControls();

  },

  /**
   *
   */
  'create' : function(container) {

    this.container = container;
    if(!Ed3d.withHudPanel) return;

    this.container = container;

    $('#'+this.container).append('<div id="hud"></div>');
    $('#hud').append(
      '<div>'+
      '    <h2>Infos</h2>'+
      '     Distance to Sol:  <span id="distsol"></span> ly<br />'+
	  '	    Distance to current:  <span id="distcur"></span> ly'+
      '    <div id="coords" class="coords">'+
      '      <span id="cx"></span><span id="cy"></span><span id="cz"></span></div>'+
      '      <p id="infos"></p>'+
      '    </div>'+
      /*'  <div id="search">'+
      '    <h2>Search</h2>'+
      '    <input type="text" />'+
      '  </div>'+*/
      '  <div id="filters">'+
      '  </div>'+
      '</div>'
    );
    $('#'+this.container).append('<div id="systemDetails" style="display:none;"></div>');
	$('#'+this.container).append('<div id="navDetails" style="display:none;"></div>');
    $('#'+this.container).append(

      '  <div id="controls">'+
	  '    <div id="options" style="display:none;"></div>'+
      '    <a href="#" data-view="3d" class="view selected">3D</a>'+
      '    <a href="#" data-view="top" class="view">2D</a>'+
	  '    <a href="#" data-view="infos" class="'+(Ed3d.showGalaxyInfos ? 'selected' : '')+'">i</a>'+
      '    <a href="#" data-view="options"><img src="/style/img/map_settings.png" alt="settings" style="vertical-align:middle;margin-bottom:3px;" /></a>'+
      '  </div>'
    );
	this.createSubOptions();

  },

  /**
   * Create option panel
   */
  'createSubOptions' : function() {

    //-- Toggle milky way
    $( "<a></a>" )
      .addClass( "sub-opt opt_active" )
      .attr('href','#')
      .html('Toggle Milky Way')
      .click(function() {
        var state = Galaxy.milkyway[0].visible;
        Galaxy.milkyway[0].visible = !state;
        Galaxy.milkyway[1].visible = !state;
		Galaxy.milkyway2D.visible  = !state;
        $(this).toggleClass('opt_active');
      })
      .appendTo( "#options" );

    //-- Toggle Grid
    $( "<a></a>" )
      .addClass( "sub-opt opt_active" )
      .attr('href','#')
      .html('Toggle grid')
      .click(function() {
        Ed3d.grid1H.toggleGrid();
        Ed3d.grid1K.toggleGrid();
        Ed3d.grid1XL.toggleGrid();
        $(this).toggleClass('opt_active');
      })
      .appendTo( "#options" );

  },


  /**
   * Controls init for camera views
   */
  'initControls' : function() {

    $('#controls a').click(function(e) {

      if($(this).hasClass('view')) {
        $('#controls a.view').removeClass('selected')
        $(this).addClass('selected');
      }

      var view = $(this).data('view');



      switch(view) {

        case 'top':
          Ed3d.isTopView = true;
          var moveFrom = {x: camera.position.x, y: camera.position.y , z: camera.position.z};
          var moveCoords = {x: controls.center.x, y: controls.center.y+500, z: controls.center.z};
          HUD.moveCamera(moveFrom,moveCoords);
          break;

        case '3d':

          Ed3d.isTopView = false;
          var moveFrom = {x: camera.position.x, y: camera.position.y , z: camera.position.z};
          var moveCoords = {x: controls.center.x-100, y: controls.center.y+500, z: controls.center.z+500};
          HUD.moveCamera(moveFrom,moveCoords);
          break;

        case 'infos':
          if(!Ed3d.showGalaxyInfos) {
            Ed3d.showGalaxyInfos = true;
            Galaxy.infosShow();
          } else {
            Ed3d.showGalaxyInfos = false;
            Galaxy.infosHide();
          }
          $(this).toggleClass('selected');
          break;

        case 'options':
          $('#options').toggle();
          break;
      }


    });

  },

  /**
   * Move camera to a target
   */
  'moveCamera' : function(from, to) {

    Ed3d.tween = new TWEEN.Tween(from, {override:true}).to(to, 800)
      .start()
      .onUpdate(function () {
        camera.position.set(from.x, from.y, from.z);
      })
      .onComplete(function () {
        controls.update();
      });
  },
  /**
   *
   */
  'initHudAction' : function() {

    //-- Disable 3D controls when mouse hover the Hud
    $( "canvas" ).hover(
      function() {
        controls.enabled = true;
      }, function() {
        controls.enabled = false;
      }
    );

    //-- Disable 3D controls when mouse hover the Hud
    $( "#hud" ).hover(
      function() {
        controls.enabled = false;
      }, function() {
        controls.enabled = true;
      }
    );
    $( "#systemDetails" ).hide();
	$( "#navDetails" ).hide();

    //-- Add Count filters
    $('.map_filter').each(function(e) {
      var idCat = $(this).data('filter');
      var count = Ed3d.catObjs[idCat].length;
      if(count>1) $(this).append(' ('+count+')');
    });

    //-- Add map filters
    $('.map_filter').click(function(e) {
      e.preventDefault();
      var idCat = $(this).data('filter');
      var active = $(this).data('active');
      active = (Math.abs(active-1));

      if(!Ed3d.hudMultipleSelect) {

        $('.map_filter').addClass('disabled');

        $(System.particleGeo.vertices).each(function(index, point) {
          point.visible  = 0;
          point.filtered = 0;
          System.particleGeo.colors[index] = new THREE.Color('#111111');
          active = 1;
        });

      }

      $(Ed3d.catObjs[idCat]).each(function(key, indexPoint) {

        obj = System.particleGeo.vertices[indexPoint];

        System.particleGeo.colors[indexPoint] = (active==1)
          ? obj.color
          : new THREE.Color('#111111');

        obj.visible = (active==1);
        obj.filtered = (active==1);

        System.particleGeo.colorsNeedUpdate = true;


      });
      $(this).data('active',active);
      $(this).toggleClass('disabled');

      //-- If current selection is no more visible, disable active selection
      //if(Action.oldSel != null && !Action.oldSel.visible) Action.disableSelection();
      //Action.moveInitalPosition();
    });


    //-- Add map link
    $('.map_link').click(function(e) {

      e.preventDefault();
      var elId = $(this).data('route');
      Action.moveToObj(routes[elId]);
    });

    $('.map_link span').click(function(e) {

      e.preventDefault();

      var elId = $(this).parent().data('route');
      routes[elId].visible = !routes[elId].visible;
    });

  },

  'initFilters' : function(categories) {

    var grpNb = 1;
    $.each(categories, function(typeFilter, values) {

      if(typeof values === "object" ) {
        var groupId = 'group_'+grpNb;



        $('#filters').append('<h2>'+typeFilter+'</h2>');
        $('#filters').append('<div id="'+groupId+'"></div>');

        $.each(values, function(key, val) {
          HUD.addFilter(groupId, key, val);
          Ed3d.catObjs[key] = []
        });
        grpNb++;
      }

    });


  },


  /**
   * Remove filters list
   */

  'removeFilters' : function() {

    $('#hud #filters').html('');

  },


  /**
   *
   */
  'addFilter' : function(groupId, idCat, val) {

    //-- Add material, if custom color defined, use it
    var back = '#fff';
    if(val.color != undefined) {
      Ed3d.addCustomMaterial(idCat, val.color);
      back = '#'+val.color;
    }

    //-- Add html link
    $('#'+groupId).append(
      '<a class="map_filter" data-active="1" data-filter="' + idCat + '">'+
      '<span class="check" style="background:'+back+'"> </span>' + val.name + '</a>'
    );
  },

  /**
   *
   */
  'openHudDetails' : function() {
    $('#hud').hide();
    $('#systemDetails').show().hover(
      function() {
        controls.enabled = false;
      }, function() {
        controls.enabled = true;
      }
    );
    $('#navDetails').show().hover(
      function() {
        controls.enabled = false;
      }, function() {
        controls.enabled = true;
      }
    );
  },
  /**
   *
   */
  'closeHudDetails' : function() {
    $('#hud').show();
    $('#systemDetails').hide();
	$('#navDetails').hide();
  },

  /**
   * Create a Line route
   */
  'setRoute' : function(idRoute, nameR) {
    $('#routes').append('<a class="map_link" data-route="' + idRoute + '"><span class="check"> </span>' + nameR + '</a>');
  },



  /**
   *
   */

  'setInfoPanel' : function(index, point) {

	fromx = $('#curx').html();
	fromy = $('#cury').html();
	fromz = $('#curz').html();

	var distance = Math.round(Math.sqrt(Math.pow((point.x-(fromx)),2)+Math.pow((point.y-(fromy)),2)+Math.pow((point.z-(fromz)), 1)));

    $('#systemDetails').html(
      '<h2><a class="hud" href="'+encodeURI('/system.php?system_name='+replaceAll(point.name, " ", "+")+'')+'">'+point.name+'</a></h2>'+
      '<div class="map_info">'+
      '  <span>Distance: '+distance+' ly</span></div>'+
      '  <p id="infos"></p>'+
      '</div>'+
      (point.infos != undefined ? '<div>'+point.infos+'</div>' : '')
    );

    $('#navDetails').html(
      '<div id="nav">'+
      '</div>'
    );

    //-- Add navigation

    $('<a/>', {'html': '<'})
    .click(function(){Action.moveNextPrev(index-1, -1);})
    .appendTo("#nav");

    $('<a/>', {'html': 'X'})
    .click(function(){HUD.closeHudDetails();})
    .appendTo("#nav");

    $('<a/>', {'html': '>'})
    .click(function(){Action.moveNextPrev(index+1, 1);})
    .appendTo("#nav");

  },


  /**
   * Add Shape text
   */

  'addText' : function(id, textShow, x, y, z, size, addToObj) {

    if(addToObj == undefined) addToObj = scene;

    var textShapes = THREE.FontUtils.generateShapes(textShow, {
      'font': 'helvetiker',
      'weight': 'normal',
      'style': 'normal',
      'size': size,
      'curveSegments': 100
    });

    var textGeo = new THREE.ShapeGeometry(textShapes);

    if(Ed3d.textSel[id] == undefined) {
      var textMesh = new THREE.Mesh(textGeo, new THREE.MeshBasicMaterial({
        color: 0xffffff
      }));
    } else {
      var textMesh = Ed3d.textSel[id];
    }

    textMesh.geometry = textGeo;
    textMesh.geometry.needsUpdate = true;

    textMesh.position.set(x, y, z);

    Ed3d.textSel[id] = textMesh;
    addToObj.add(textMesh);


  }

}
