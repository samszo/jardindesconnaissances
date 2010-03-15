package jardin
{
	import flash.display.DisplayObject;
	import flash.display.Sprite;
	import flash.events.MouseEvent;
	
	import mx.controls.LinkButton;

	public class BulleTerre extends Sprite
	{

		private var _tag:LinkButton;

		public function BulleTerre()
		{
			super();
			//_tag = tag;
			initialize();
		}
		
		private function initialize():void
		{

			var _circle0:Sprite = new Sprite();
			_circle0.x = 100;
			_circle0.y = 100;
			_circle0.graphics.clear();
			_circle0.graphics.beginFill( 0xFFFFFF, 1 )
			_circle0.graphics.drawCircle( 110, 100, 250 );
			_circle0.graphics.endFill();
			_circle0.addEventListener( MouseEvent.MOUSE_DOWN, onCircleMouseDown );
			addChild(DisplayObject(_circle0));
		}

		private function onCircleMouseDown( event:MouseEvent ):void
		{
			var circle:Sprite = Sprite( event.target );
			circle.startDrag( false );
		}

	}
}