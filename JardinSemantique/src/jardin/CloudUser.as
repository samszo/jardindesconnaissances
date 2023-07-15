package jardin
{
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.net.*;

	public class CloudUser extends Sprite
	{
		public var minOccurTag:int=1;
		public var MinFontSize:Number = 6;
		public var MaxFontSize:Number = 36;
		public var MaxX:Number = 800;
		public var MaxY:Number = 0;
		 
		public function CloudUser(url:String)
		{
			loadUserTag(url);
			//addEventListener( MouseEvent.MOUSE_DOWN, onMouseDown );
			//addEventListener( MouseEvent.MOUSE_UP, onMouseUp );
			//addEventListener( MouseEvent.MOUSE_MOVE, onMouseMove);

		}

		private function makeFond():void
		{
			var fond:Sprite = new Sprite();
			fond.graphics.clear();
			fond.graphics.beginFill(0xFF0000,0.01)
			fond.graphics.drawRect(0,0,this.MaxX,this.MaxY);
			fond.graphics.endFill();
			this.addChildAt(fond,0);
			this.width = this.MaxX;
			this.height = this.MaxY
			//this.addChild(fond);
			
		}


		private function onMouseMove( event:MouseEvent ):void
		{
			//this.x +=1;
		}


		private function onMouseDown( event:MouseEvent ):void
		{
			this.startDrag( false );
		}
		
		private function onMouseUp( event:MouseEvent ):void
		{
			this.stopDrag();
		}

		private function loadUserTag(url:String):void    	
		{
			
		    var urlRequest:URLRequest = new URLRequest(url);
			trace ("CloudUser:init:query=" +url);
		    urlRequest.method = URLRequestMethod.POST;
		    var urlLoader:URLLoader = new URLLoader(urlRequest);
		    urlLoader.addEventListener("complete", resultHandler);
			
		}
		
		private function resultHandler(event:Event):void    	
		{
			try{
				buildTagCloud(new XML(event.target.data));
				makeFond();
			}catch (err:Error){
			 	// code to react to the error
				trace ("CloudUser:resultHandler:erreur="+err.message);
			}
	    } 

		private function buildTagCloud(xml:XML):void
		{
						
			//récupération des item du flux rss
			var x:XMLList = xml.channel.item;
			
			var max:int;
			max = getMaxOccur(x);
			//boucle sur chaque item
			var i:Number = 0; 
			var xBulle:Number = 0; 
			var yBulle:Number = 0; 
			for each (var item:XML in x){
				var title:String = item.title;
				var nb:int = item.description;
				var url:String = item.guid;
				var bulle:BulleTerre;
				if(nb>minOccurTag){
					bulle = new BulleTerre(title, getFontHeight(nb, max), url);
					/*
					if(pluie){
						//this.addChild(createTagLink(title, getFontHeight(nb, max),url));
						bulle.limiteNuageDroite = this.width;
						bulle.limiteTerre = this.limiteTerre;
						this.addChild(bulle);
					}else{
						//tagContainer.addChild(createTagLink(title, getFontHeight(nb, max),url));
						bulle.pluie=false;
						tagContainer.addChild(bulle);
					}
					*/
					if(xBulle>MaxX){
						xBulle=0; 
						yBulle += bulle.height 
					} else {
						xBulle += bulle.width;
					}
					bulle.x = xBulle;
					bulle.y = yBulle;
					addChild(bulle);
					i++;				
				}
			}
			MaxY = yBulle;
			
		}
		
        // get the maximum times a tag occurs for scaling.
		private function getMaxOccur(x:XMLList):Number
		{
			var max:int = 1;
			
			for each (var item:XML in x){
				var n:int = item.description;
				if(n > max) max = n;
			} 
			
			return max;
		}

		// scaling between occurences and font size
		// @occurences - the # of times a tag has occured in the xml
		// @maxoccur - the largest occurence value 
		public function getFontHeight(occurences:int, maxoccur:int):int
		{
			var interval:int = Math.round((MaxFontSize - MinFontSize) / 3);
			var ht:int = MinFontSize;
			
			if(occurences == maxoccur)
			{
				// largest size
				ht = MaxFontSize;
			}
			else if(occurences >= (maxoccur / 2))
			{
				ht = MaxFontSize - interval;
			}
			else if(occurences > 1)
			{
				ht = MinFontSize + interval;
			}
			else
			{
				// smallest/default size
				ht = MinFontSize;
			}
			
			return ht;
		}


	}
}