package jardin
{
	import flash.display.Sprite;
	import flash.events.MouseEvent;
	import flash.text.TextField;
	import flash.text.TextFieldAutoSize;
	import flash.text.TextFormat;
        	
	public class BulleTerre extends Sprite
	{

		public var _tag:TextField;
		public var _texte:String;
		public var _size:int;
		public var _url:String;
		public var _bulle:Sprite;
		public var _margeX:int=20;
		public var _margeY:int=20;
		public var _over:Boolean=false;
		

		public	var bousso:Boussole;

		public function BulleTerre(texte:String, size:int, url:String)
		{
			super();
			_texte = texte;
			_size = size;
			_url = url;
			makeTag();
			makeBulle();
			//makeBrique();

			bousso = new Boussole(this);
			bousso.x = _bulle.width/2;
			bousso.y = _bulle.height/2;
			bousso.visible=false;
			addChild(bousso);


			addEventListener( MouseEvent.MOUSE_DOWN, onMouseDown );
			addEventListener( MouseEvent.MOUSE_UP, onMouseUp );
			addEventListener( MouseEvent.MOUSE_OVER, onMouseOver);
			addEventListener( MouseEvent.MOUSE_OUT, onMouseOut);
			addEventListener( MouseEvent.CLICK, mouseClickHandler);

			
		}


		private function makeTag():void
		{
            _tag = new  TextField();
            _tag.text = _texte;
            _tag.x = _margeX;
            _tag.y = _margeY;
            _tag.autoSize = TextFieldAutoSize.LEFT;
            _tag.background = true;
            _tag.border = true;
  			_tag.selectable = false;
  
            var format:TextFormat = new TextFormat();
            format.font = "Verdana";
            format.color = 0xFF0000;
            format.size = _size;
            format.underline = true;

            _tag.defaultTextFormat = format;
            addChild(_tag);
			
		}

		private function makeBulle():void
		{
			_bulle = new Sprite();
			_bulle.x = _margeX;
			_bulle.y = _margeY;
			_bulle.graphics.clear();
			_bulle.graphics.beginFill( 0xFFFFFF, 0.3)
			_bulle.graphics.drawEllipse(-_margeY, -_margeY, _tag.width+(_margeY*2), _tag.height+(_margeY*2));			
			_bulle.graphics.endFill();
			addChildAt(_bulle,0);
			
		}

		private function makeBrique():void
		{
			_bulle = new Sprite();
			_bulle.x = _margeX;
			_bulle.y = _margeY;
			_bulle.graphics.clear();
			_bulle.graphics.beginFill( 0xFFFFFF, 0.3)
			_bulle.graphics.drawRect(0, 0, _tag.width+(_margeY*2), _tag.height+(_margeY*2));			
			_bulle.graphics.endFill();
			addChildAt(_bulle,0);
			
		}

		private function onMouseDown( event:MouseEvent ):void
		{
			this.startDrag( false );
		}
		
		private function onMouseUp( event:MouseEvent ):void
		{
			this.stopDrag();
		}
        private function mouseClickHandler(event:MouseEvent):void {
            bousso.drawPoly(10,6,0,0);
            bousso._pm.x = event.stageX;
			bousso._pm.y = event.stageY;
			bousso._pm.show();
            //event.updateAfterEvent();
        }
		private function onMouseOver( event:MouseEvent ):void
		{
			/*
			if(!_over){
				_over = true;
				this.tabIndex = 0;
				this.scaleX=this.scaleX*6;
				this.scaleY=this.scaleY*6;
				this.x = this.mouseX;				
				this.y = this.mouseY;				
				trace ("BulleTerre:onMouseOver:scaleX="+scaleX+" - scaleY="+scaleY);
			}
			*/
            bousso.visible=true;
            
		}

		private function onMouseOut( event:MouseEvent ):void
		{
			/*
			if(_over){
				_over = false;
				this.scaleX=1;
				this.scaleY=1;
				_jardin.AddTag(this);
				trace ("BulleTerre:onMouseOut:scaleX="+scaleX+" - scaleY="+scaleY);
			}
			*/
            bousso.visible = false;			
		}

	}
}
