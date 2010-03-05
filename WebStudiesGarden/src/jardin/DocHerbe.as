package jardin
{
    import flash.display.Sprite;
    import flash.events.*;
    import flash.net.URLLoader;
    import flash.net.URLRequest;
    
    import mx.controls.LinkButton;
	
	public class DocHerbe extends Sprite
	{
		public var _url:String;
		public var _tgTerre:TagCloud;
		public var _tgCiel:TagCloud;
		public var _tag:LinkButton;

		[Embed(source="bourgeon.png")]
        [Bindable]
        public var bourgeon:Class; 
		[Embed(source="bourgeonL.png")]
        [Bindable]
        public var bourgeonL:Class; 

		public function DocHerbe(url:String, tgT:TagCloud, tgC:TagCloud, tag:LinkButton)
		{
			_url = url
			_tgTerre=tgT;
			_tgCiel=tgC;
			_tag=tag;

        }
        public function send():void
        {
            var request:URLRequest = new URLRequest(_url);
            var variables:URLLoader = new URLLoader();
            
            variables.addEventListener(Event.COMPLETE, completeHandler);
            try
            {
                variables.load(request);
            } 
            catch (error:Error)
            {
                trace("Unable to load URL: " + error);
            }
        	
        }

        private function completeHandler(event:Event):void
        {
	        var loader:URLLoader = URLLoader(event.target);
   			build(new XML(loader.data));	        
        }

        private function build(xml:XML):void
		{
						
			//récupération des item du flux rss
			var items:XMLList = xml.channel.item;

			//création de la tige	        
	        _tgTerre.graphics.beginFill(4823581,0.5);
	        _tgTerre.graphics.lineStyle(1, 7929610,0.5);
   			var brgSize:int=16;
	        var hautHerbe:Number = brgSize*items.length();
	        var h:Number= hautHerbe+_tag.y+(_tag.height*2);
	        var x:Number=_tag.x+(_tag.width/2)
	        var y:Number=_tag.y-h+30;//30 = y de tagContainer
	        _tgTerre.graphics.drawRect(x, y, 3, h);
	        _tgTerre.graphics.endFill();

			var u:String;
			if(!items.length()){
				//il n'y a qu'un post pour le tag
		        u=xml.channel.link;
				_tgCiel.drawBourgeon(x,y+_tgTerre.y+(brgSize*i),u,brgSize,bourgeon);				
			}else{				
				//boucle sur chaque item
				var i:int = 1;
				for each (var item:XML in items){
			        //création du bourgeon dans le ciel
			        u=item.link;
					if (i%2 != 0) {
						_tgCiel.drawBourgeon(x,y+_tgTerre.y+(brgSize*i),u,brgSize,bourgeon);
					} else {
						_tgCiel.drawBourgeon(x+brgSize,y+_tgTerre.y+(brgSize*i),u,brgSize,bourgeonL);
					}			        
					i++;
				}
			}
			
		}
		

	}
}