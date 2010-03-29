package {
	import flash.display.DisplayObject;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.geom.Point;
	import flash.geom.Rectangle;
	import flash.text.TextField;
	import flash.text.TextFieldAutoSize;
	import flash.text.TextFormat;
	
	import jardin.Boussole;
	import jardin.BulleTerre;
	import jardin.CloudUser;
	
	import ws.tink.display.HitTest;

	[SWF(backgroundColor="#000000", frameRate="30")]
	public class SemanticGarden extends Sprite
	{
		public var _intersection:Sprite = new Sprite();
		public var _hit:Boolean;
		public var tcUser:CloudUser 
		public	var bousso:Boussole;
		private var _objExplo:DisplayObject;
		private var point1:Point= new Point(0,0);
			
		public function SemanticGarden()
		{
			
			//var tcUser:CloudUser = new CloudUser("http://localhost/evalactisem/tmp/network/luckysemiosis.xml");
			tcUser = new CloudUser("http://localhost/evalactisem/tmp/tags/luckysemiosis.xml");
			addChild(tcUser);
			tcUser.addEventListener(MouseEvent.MOUSE_OVER, mouseOverHandler);
            tcUser.addEventListener(MouseEvent.MOUSE_OUT, mouseOutHandler);
            //Mouse.hide();
            tcUser.addEventListener(MouseEvent.MOUSE_MOVE, mouseMoveHandler);
	
			bousso = new Boussole(tcUser);
			bousso.visible = false;
			addChild(bousso);
			
			stage.addEventListener(Event.MOUSE_LEAVE, mouseLeaveHandler);
			//stage.addEventListener(MouseEvent.MOUSE_MOVE,  mouseMoveHandler);

			_objExplo = tcUser;
			makeCadre();
			makeButtons();

		}

		public function AddTag(t:BulleTerre):void
		{
			var tag:BulleTerre = new BulleTerre(this, t._texte, t._size, t._url);
			tag.x = -140;
			tag.y = 100;
			this.addChildAt(tag,0);
		}

         private function mouseOverHandler(event:MouseEvent):void {
            trace("mouseOverHandler");
            //Mouse.hide();
            tcUser.addEventListener(MouseEvent.MOUSE_MOVE, mouseMoveHandler);
        }

        private function mouseOutHandler(event:MouseEvent):void {
            trace("mouseOutHandler");
            //Mouse.show();
            tcUser.removeEventListener(MouseEvent.MOUSE_MOVE, mouseMoveHandler);
            bousso.visible = false;
        }

        private function mouseMoveHandler(event:MouseEvent):void {
            trace("mouseMoveHandler");
            //bousso.x = event.localX;
            //bousso.y = event.localY;
            bousso.x = event.stageX;
            bousso.y = event.stageY;
            event.updateAfterEvent();
            bousso.visible = true;
        }

        private function mouseLeaveHandler(event:Event):void {
            trace("mouseLeaveHandler");
            mouseOutHandler(new MouseEvent(MouseEvent.MOUSE_MOVE));
        }

				
		public function onStageMouseMove( event:MouseEvent ):void
		{
			//récupère la bulle qui bouge
			var bulle0:DisplayObject = DisplayObject( event.target );
			
			//test la rencontre avec les autres bulles
			var i:int;
			var bulle1:DisplayObject;
			for (i = 0; i < this.numChildren; i++)
			{
				bulle1 = this.getChildAt(i);
				if(bulle0!=bulle1){
					TestHitDraw(bulle0,bulle1);
				}
			}
			
			
		}


		private function moveTagUserGauche(e:Event):void {
			point1.x=mouseX;
			point1.y=mouseY;
		 	var hs:Object=e.target;
		 
			if (hs.hitTestPoint(point1.x,point1.y,true)) {
				//trace ("BulleTerre:watchForMouseEntry:hitest=");
			   	_objExplo.x -= 6;
			}
		}

		private function moveTagUserDroite(e:Event):void {
			point1.x=mouseX;
			point1.y=mouseY;
		 	var hs:Object=e.target;
		 
			if (hs.hitTestPoint(point1.x,point1.y,true)) {
				//trace ("BulleTerre:watchForMouseEntry:hitest=");
			   	_objExplo.x += 6;
			}
		}

		private function moveTagUserBas(e:Event):void {
			point1.x=mouseX;
			point1.y=mouseY;
		 	var hs:Object=e.target;
		 
			if (hs.hitTestPoint(point1.x,point1.y,true)) {
			   	_objExplo.y -= 6;
			}
		}

		private function moveTagUserHaut(e:Event):void {
			point1.x=mouseX;
			point1.y=mouseY;
		 	var hs:Object=e.target;
		 
			if (hs.hitTestPoint(point1.x,point1.y,true)) {
			   	_objExplo.y += 6;
			}
		}

		private function makeCadre():void
		{
			//création des bandes de déplacement
			var cadre:Sprite = new Sprite();		
			cadre.graphics.clear();
			cadre.graphics.beginFill( 0xFFFFFF, 0.5 );
			cadre.graphics.drawRect(-6,0,6,300);
			cadre.graphics.endFill();
			cadre.addEventListener(Event.ENTER_FRAME, moveTagUserDroite );
			this.addChild(cadre);

			cadre = new Sprite();		
			cadre.graphics.clear();
			cadre.graphics.beginFill( 0xFFFFFF, 0.5 );
			cadre.graphics.drawRect(600,0,6,300);
			cadre.graphics.endFill();
			cadre.addEventListener(Event.ENTER_FRAME, moveTagUserGauche);
			this.addChild(cadre);

			cadre = new Sprite();		
			cadre.graphics.clear();
			cadre.graphics.beginFill( 0xFFFFFF, 0.5 );
			cadre.graphics.drawRect(-6,300,612,6);
			cadre.graphics.endFill();
			cadre.addEventListener(Event.ENTER_FRAME, moveTagUserBas);
			this.addChild(cadre);

			cadre = new Sprite();		
			cadre.graphics.clear();
			cadre.graphics.beginFill( 0xFFFFFF, 0.5 );
			cadre.graphics.drawRect(0,0,600,6);
			cadre.graphics.endFill();
			cadre.addEventListener(Event.ENTER_FRAME, moveTagUserHaut);
			this.addChild(cadre);

			//création des bandes de masquage de l'objet à explorer
			cadre = new Sprite();		
			cadre.graphics.clear();
			cadre.graphics.beginFill(0x000000);
			cadre.graphics.drawRect(-606,0,600,600);
			cadre.graphics.endFill();
			
			this.addChild(cadre);

			cadre = new Sprite();		
			cadre.graphics.clear();
			cadre.graphics.beginFill(0x000000);
			cadre.graphics.drawRect(-6,306,612,600);
			cadre.graphics.endFill();
			this.addChild(cadre);
			
			cadre = new Sprite();		
			cadre.graphics.clear();
			cadre.graphics.beginFill(0x000000);
			cadre.graphics.drawRect(606,0,600,600);
			cadre.graphics.endFill();
			this.addChild(cadre);			
		}

		private function makeButtons():void
		{

			var choixBousso:Boussole = new Boussole(this);
			choixBousso.x = -60;
			choixBousso.y = 60;
			this.addChild(choixBousso);
			
            var format:TextFormat = new TextFormat();
            format.font = "Verdana";
            format.color = 0xFF0000;
            format.size = 64;
            format.underline = true;

            var zM:TextField = new TextField();
            zM.text = "-";
            zM.x = -100;
            zM.y = 0;
            zM.autoSize = TextFieldAutoSize.LEFT;
            zM.background = true;
            zM.border = true;
            zM.defaultTextFormat = format;
			zM.addEventListener(Event.ENTER_FRAME,onZoomMoins);
            addChild(zM);

            var zP:TextField = new TextField();
            zP.text = "+";
            zP.x = -120;
            zP.y = 0;
            zP.autoSize = TextFieldAutoSize.LEFT;
            zP.background = true;
            zP.border = true;
            zP.defaultTextFormat = format;
			zP.addEventListener(Event.ENTER_FRAME,onZoomPlus);
            addChild(zP);
			
		}

		public function onZoomMoins(e:Event ):void
		{
			point1.x=mouseX;
			point1.y=mouseY;
		 	var hs:Object=e.target;
		 
			if (hs.hitTestPoint(point1.x,point1.y,true)) {
				_objExplo.scaleX = _objExplo.scaleX/1.01; 
				_objExplo.scaleY = _objExplo.scaleY/1.01; 
			}
			
		}

		public function onZoomPlus(e:Event ):void
		{
			point1.x=mouseX;
			point1.y=mouseY;
		 	var hs:Object=e.target;
		 
			if (hs.hitTestPoint(point1.x,point1.y,true)) {
				_objExplo.scaleX = _objExplo.scaleX*1.01; 
				_objExplo.scaleY = _objExplo.scaleY*1.01; 
			}
		}
		
		
		
		public function TestHitDraw(bulle0:DisplayObject, bulle1:DisplayObject):void
		{
			var intersection:Rectangle = HitTest.complexIntersectionRectangle(bulle0, bulle1, 5 );
			var hit:Boolean = ( intersection.width > 0 && intersection.height > 0 );
			
			_intersection.graphics.clear();
			if( hit )
			{
				_intersection.graphics.beginFill( 0xFF0000, 0.5 );
				_intersection.graphics.drawRect( intersection.x, intersection.y, intersection.width, intersection.height );
				_intersection.graphics.endFill();
			}
			
			if( hit != _hit )
			{
				_hit = hit;
				var color:Number = ( _hit ) ? 0x00FF00 : 0xFFFFFF;
				//draw( bulle0, color );
				//draw( bulle1, color );
			}
			
		}
		
	}
}
