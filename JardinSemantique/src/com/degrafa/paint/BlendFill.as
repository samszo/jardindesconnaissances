package com.degrafa.paint
{
	import com.degrafa.core.DegrafaObject;
	import com.degrafa.core.IBlend;
	import com.degrafa.core.IGraphicsFill;
	
	import flash.display.BlendMode;
	import flash.display.Graphics;
	import flash.geom.Rectangle;
	
	import mx.events.PropertyChangeEvent;
	import mx.graphics.IFill;
	
	[DefaultProperty("fill")]
	[Bindable(event="propertyChange")]
	/**
	 * Used to wrap standard IFill objects for use in a ComplexFill.
	 * The blendMode is only recognized in the context of a ComplexFill.
	 */
	public class BlendFill extends DegrafaObject implements IGraphicsFill, IBlend
	{
		
		// property backing vars
		private var _blendMode:String;
		private var _fill:IFill;
		
		
		//**************************************
		// Public Properties
		//**************************************
		
		/**
		 * The blendMode used to render this layer in a ComplexFill.
		 * You may use any constant provided in the flash.display.BlendMode class.
		 */
		public function get blendMode():String { return _blendMode; }
		public function set blendMode(value:String):void {
			if(_blendMode != value) {
				initChange("blendMode", _blendMode, _blendMode = value, this);
			}
		}
		
		/**
		 * The IFill which this BlendFill wraps.
		 */
		public function get fill():IFill { return _fill; }
		public function set fill(value:IFill):void {
			if(_fill != value) {
				if(_fill is IGraphicsFill) {
					(_fill as IGraphicsFill).removeEventListener(PropertyChangeEvent.PROPERTY_CHANGE, propertyChangeHandler, false);
				}
				if(value is IGraphicsFill) {
					(value as IGraphicsFill).addEventListener(PropertyChangeEvent.PROPERTY_CHANGE, propertyChangeHandler, false, 0, true);
				}
				initChange("fill", _fill, _fill = value, this);
			}
		}
		
		
		//*****************************************
		// Constructor
		//*****************************************
		
		public function BlendFill(fill:IFill = null, blendMode:String = "normal"):void {
			this.fill = fill;
			this.blendMode = blendMode;
		}
		
		
		//*****************************************
		// Public Methods
		//*****************************************
		
		public function begin(graphics:Graphics, rectangle:Rectangle):void {
			if(fill != null) {
				fill.begin(graphics, rectangle);
			}
		}
		
		public function end(graphics:Graphics):void {
			if(fill != null) {
				fill.end(graphics);
			}
		}
		
		
		//*************************************
		// Event Handlers
		//*************************************
		
		private function propertyChangeHandler(event:PropertyChangeEvent):void{
			dispatchEvent(event);
		}
		
	}
}