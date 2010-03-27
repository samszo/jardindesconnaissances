package {
	import flash.display.DisplayObject;
	import flash.display.Sprite;
	import flash.events.MouseEvent;
	import flash.geom.Rectangle;
	import flash.text.TextField;
	import flash.text.TextFieldAutoSize;
	import flash.text.TextFormat;
	
	import jardin.BulleTerre;
	import jardin.CloudUser;
	
	import ws.tink.display.HitTest;

	[SWF(backgroundColor="#000000", frameRate="30")]
	public class SemanticGarden extends Sprite
	{
		public var _intersection:Sprite = new Sprite();
		public var _hit:Boolean;
		public var tcUser:CloudUser 
		
		public function SemanticGarden()
		{
			
			//var tcUser:CloudUser = new CloudUser("http://localhost/evalactisem/tmp/network/luckysemiosis.xml");
			tcUser = new CloudUser("http://localhost/evalactisem/tmp/tags/luckysemiosis.xml");
			addChild(tcUser);
			
			makeButtons();
		
		}

		public function AddTag(t:BulleTerre):void
		{
			var tag:BulleTerre = new BulleTerre(this, t._texte, t._size, t._url);
			tag.x = -140;
			tag.y = 100;
			this.addChildAt(tag,0);
		}
		
		
		private function makeButtons():void
		{

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
			zM.addEventListener(MouseEvent.MOUSE_OVER,onZoomMoins);
            addChild(zM);

            var zP:TextField = new TextField();
            zP.text = "+";
            zP.x = -160;
            zP.y = 0;
            zP.autoSize = TextFieldAutoSize.LEFT;
            zP.background = true;
            zP.border = true;
            zP.defaultTextFormat = format;
			zP.addEventListener(MouseEvent.MOUSE_OVER,onZoomPlus);
            addChild(zP);
			
		}

		public function onZoomMoins(event:MouseEvent ):void
		{
			tcUser.scaleX = tcUser.scaleX/1.2; 
			tcUser.scaleY = tcUser.scaleY/1.2; 
		}

		public function onZoomPlus(event:MouseEvent ):void
		{
			tcUser.scaleX = tcUser.scaleX*1.2; 
			tcUser.scaleY = tcUser.scaleY*1.2; 
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
