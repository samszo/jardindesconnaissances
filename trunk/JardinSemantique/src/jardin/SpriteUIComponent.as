package jardin
{
	import mx.core.UIComponent;
	
	import flash.display.Sprite;
	
	//http://userflex.wordpress.com/2008/06/12/sprite-uicomponent/
	public class SpriteUIComponent extends UIComponent
	{
		public function SpriteUIComponent(sprite : Sprite)
		{
			super();
			
			this.percentHeight = 100;
			this.percentWidth = 100;
    		
    		//explicitHeight = 600;
    		//explicitWidth = 800;
        		
    		addChild (sprite);			
		}
		
	}
}