<?php
$fn = new Fonksiyonlar();

$yetkili_subeler = $_SESSION[ 'subeler' ];

$SQL_oku = <<< SQL
SELECT
	 r.*
	,r.randevu_tarihi as randevu_baslama
	,DATE_ADD(r.randevu_tarihi, INTERVAL 1 HOUR) as randevu_bitis
	,arac.arac_no
FROM
	tb_randevular AS r
LEFT JOIN 
	tb_araclar as arac ON arac.id = r.arac_id
WHERE 
	r.aktif = 1 
AND
	CASE
		WHEN ? = 1 THEN TRUE
		ELSE r.sube_id in ($yetkili_subeler)
	END
SQL;

$randevular				= $vt->select( $SQL_oku, array( $_SESSION[ 'super' ]  ) );

?>

<div id="calendarModal" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div id = "baslik_renk"class="modal-header">
        <h5 id="modalTitle" class="modal-title">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p id="modalBody">Modal body text goes here.</p>
      </div>
      <div class="modal-footer">
        <a id="eventUrl" type="button" class="btn btn-warning" href="deneme">Randevu Düzenle</a>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
      </div>
    </div>
  </div>
</div>

        <div class="row">
          <!--div class="col-md-3">
            <div class="sticky-top mb-3">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title">Draggable Events</h4>
                </div>
                <div class="card-body">
                  <div id="external-events">
                    <div class="external-event bg-success">Lunch</div>
                    <div class="external-event bg-warning">Go home</div>
                    <div class="external-event bg-info">Do homework</div>
                    <div class="external-event bg-primary">Work on UI design</div>
                    <div class="external-event bg-danger">Sleep tight</div>
                    <div class="checkbox">
                      <label for="drop-remove">
                        <input type="checkbox" id="drop-remove">
                        remove after drop
                      </label>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Create Event</h3>
                </div>
                <div class="card-body">
                  <div class="btn-group" style="width: 100%; margin-bottom: 10px;">
                    <ul class="fc-color-picker" id="color-chooser">
                      <li><a class="text-primary" href="#"><i class="fas fa-square"></i></a></li>
                      <li><a class="text-warning" href="#"><i class="fas fa-square"></i></a></li>
                      <li><a class="text-success" href="#"><i class="fas fa-square"></i></a></li>
                      <li><a class="text-danger" href="#"><i class="fas fa-square"></i></a></li>
                      <li><a class="text-muted" href="#"><i class="fas fa-square"></i></a></li>
                    </ul>
                  </div>
                  <div class="input-group">
                    <input id="new-event" type="text" class="form-control" placeholder="Event Title">

                    <div class="input-group-append">
                      <button id="add-new-event" type="button" class="btn btn-primary">Add</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div-->
          <!-- /.col -->
          <div class="col-md-2">
          </div>
          <div class="col-md-8">
            <div class="card card-primary">
              <div class="card-body p-0">
                <!-- THE CALENDAR -->
                <div id="calendar"></div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <div class="col-md-2">
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->

<script>

  $(function () {

    /* initialize the external events
     -----------------------------------------------------------------*/
    function ini_events(ele) {
      ele.each(function () {

        // create an Event Object (https://fullcalendar.io/docs/event-object)
        // it doesn't need to have a start or end
        var eventObject = {
          title: $.trim($(this).text()) // use the element's text as the event title
        }

        // store the Event Object in the DOM element so we can get to it later
        $(this).data('eventObject', eventObject)

        // make the event draggable using jQuery UI
        $(this).draggable({
          zIndex        : 1070,
          revert        : true, // will cause the event to go back to its
          revertDuration: 0  //  original position after the drag
        })

      })
    }

    ini_events($('#external-events div.external-event'))

    /* initialize the calendar
     -----------------------------------------------------------------*/
    //Date for the calendar events (dummy data)
    var date = new Date()
    var d    = date.getDate(),
        m    = date.getMonth(),
        y    = date.getFullYear()

    var Calendar = FullCalendar.Calendar;
    var Draggable = FullCalendar.Draggable;

    //var containerEl = document.getElementById('external-events');
    //var checkbox = document.getElementById('drop-remove');
    var calendarEl = document.getElementById('calendar');

    // initialize the external events
    // -----------------------------------------------------------------
	/*
    new Draggable(containerEl, {
      itemSelector: '.external-event',
      eventData: function(eventEl) {
        return {
          title: eventEl.innerText,
          backgroundColor: window.getComputedStyle( eventEl ,null).getPropertyValue('background-color'),
          borderColor: window.getComputedStyle( eventEl ,null).getPropertyValue('background-color'),
          textColor: window.getComputedStyle( eventEl ,null).getPropertyValue('color'),
        };
      }
    });
	*/
    var calendar = new Calendar(calendarEl, {
      headerToolbar: {
        left  : 'prev,next today',
        center: 'title',		
        right : 'listMonth,dayGridMonth,timeGridWeek,timeGridDay'
      },
	eventClick: function(info) {
	  var eventObj = info.event;
		$('#modalTitle').html(eventObj.title);
		$('#modalBody').html(eventObj.extendedProps.description);
		$('#eventUrl').attr('href',eventObj.extendedProps.yonlendir);
		$('#baslik_renk').attr('class',eventObj.extendedProps.baslik_renk);
		$('#calendarModal').modal();
	},
	  initialView: 'dayGridMonth',
      themeSystem: 'bootstrap',
	  locale: 'tr',
      //Random default events
	  <?php $sayi = 1; foreach( $randevular[ 2 ] AS $randevu ) { 
		if( $randevu['randevu_tipi'] == 1 ){
			$renk = "#f56954";
			$bilgi ="Aracını Satmak İstiyor";
			$duzenle_linki = "?modul=randevuAracSatanlar&islem=guncelle&id=".$randevu['id'];
			$baslik_renk = "modal-header bg-danger";
			$description = "<table><tr><td colspan=3><h5><b>$bilgi</b></h5></td></tr><tr><td><b>Adı</b></td><td>:</td><td>$randevu[adi]</td></tr><tr><td><b>Soyadı</b></td><td>:</td><td>$randevu[soyadi]</td></tr><tr><td><b>Cep Telefonu</b></td><td>:</td><td>$randevu[cep_tel]</td></tr><tr><td><b>Notlar</b></td><td>:</td><td>$randevu[notlar]</td></tr></table>";
		}
		if( $randevu['randevu_tipi'] == 2 ){
			$renk = "#00a65a";
			$bilgi ="<a href=?modul=araclar&islem=detaylar&tab_no=1&id=$randevu[arac_id] >".$randevu['arac_no']."</a> Numaralı Aracı Almak İstiyor";
			$duzenle_linki = "?modul=randevuAracAlanlar&islem=guncelle&id=".$randevu['id'];
			$baslik_renk = "modal-header bg-success";
			$description = "<table><tr><td colspan=3><h5><b>$bilgi</b></h5></td></tr><tr><td><b>Adı</b></td><td>:</td><td>$randevu[adi]</td></tr><tr><td><b>Soyadı</b></td><td>:</td><td>$randevu[soyadi]</td></tr><tr><td><b>Cep Telefonu</b></td><td>:</td><td>$randevu[cep_tel]</td></tr><tr><td><b>Notlar</b></td><td>:</td><td>$randevu[notlar]</td></tr></table>";
		}
		
		$yil = date('Y',strtotime($randevu['randevu_baslama']));
		$ay = date('m',strtotime($randevu['randevu_baslama'])) - 1;
		$gun = date('d',strtotime($randevu['randevu_baslama']));
		$saat = date('H',strtotime($randevu['randevu_baslama']));
		$saat_bitis = $saat + 1;
		$dk = date('i',strtotime($randevu['randevu_baslama']));
		
		$events[] = "
        {
          title          : '$randevu[adi] $randevu[soyadi]',
          start          : new Date($yil,$ay,$gun,$saat,$dk),
          end            : new Date($yil,$ay,$gun,$saat_bitis,$dk),
          backgroundColor: '$renk', 
          borderColor    : '$renk',
          yonlendir      : '$duzenle_linki',
          baslik_renk    : '$baslik_renk',
		  description	 : '$description',
          allDay         : false
        }
		";
		$event = implode(",", $events);
	  }?>

      events: [
	  <?php	  
		echo $event;
	  ?>
      ],
      editable  : false,
      droppable : false, // this allows things to be dropped onto the calendar !!!
      /*drop      : function(info) {
        // is the "remove after drop" checkbox checked?
        if (checkbox.checked) {
          // if so, remove the element from the "Draggable Events" list
          info.draggedEl.parentNode.removeChild(info.draggedEl);
        }
      }*/
    });

    calendar.render();
    // $('#calendar').fullCalendar()
	

    /* ADDING EVENTS */
    var currColor = '#3c8dbc' //Red by default
    // Color chooser button
    $('#color-chooser > li > a').click(function (e) {
      e.preventDefault()
      // Save color
      currColor = $(this).css('color')
      // Add color effect to button
      $('#add-new-event').css({
        'background-color': currColor,
        'border-color'    : currColor
      })
    })
    $('#add-new-event').click(function (e) {
      e.preventDefault()
      // Get value and make sure it is not null
      var val = $('#new-event').val()
      if (val.length == 0) {
        return
      }

      // Create events
      var event = $('<div />')
      event.css({
        'background-color': currColor,
        'border-color'    : currColor,
        'color'           : '#fff'
      }).addClass('external-event')
      event.text(val)
      $('#external-events').prepend(event)

      // Add draggable funtionality
      ini_events(event)

      // Remove event from text input
      $('#new-event').val('')
    })
  })
</script>
