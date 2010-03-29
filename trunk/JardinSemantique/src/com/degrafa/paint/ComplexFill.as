package com.degrafa.paint
{
	import com.degrafa.core.DegrafaObject;
	import com.degrafa.core.IGraphicsFill;
	import com.degrafa.core.IBlend;
	
	import flash.display.BitmapData;
	import flash.display.Graphics;
	import flash.display.Shape;
	import flash.geom.Matrix;
	import flash.geom.Rectangle;
	
	import mx.events.PropertyChangeEvent;
	import mx.graphics.IFill;
	
	[DefaultProperty("fills")]
	
	/**
	 * Used to render multiple, layered IFill objects as a single fill.
	 * This allows complex background graphics to be rendered with a single drawing pass.
	 */
	public class ComplexFill extends DegrafaObject implements IGraphicsFill
	{
		
		//************************************
		// Static Methods
		//************************************
		
		/**
		 * Combines an IFill object with the target ComplexFill, merging ComplexFills if necessary.
		 */
		public static function add(value:IFill, target:ComplexFill):void {
			// todo: update this to account for events
			var complex:ComplexFill = target;
			if(complex == null) {
				complex = new ComplexFill();
			}
			if(complex.fills == null) {
				complex.fills = new Array();
			}
			if(value is ComplexFill) {
				for each(var fill:IFill in (value as ComplexFill).fills) {
					complex.fills.push(fill);
					complex.refresh();
				}
			} else if(value != null) {
				complex.fills.push(value);
				complex.refresh();
			}
		}
		
		
		private var shape:Shape;
		private var bitmapData:BitmapData;
		
		private var _fills:Array; // property backing var
		private var fillsChanged:Boolean; // dirty flag
		
		
		//**************************************
		// Public Properties
		//**************************************
		
		/**
		 * Array of IFill Objects to be rendered
		 */
		public function get fills():Array { return _fills; }
		public function set fills(value:Array):void {
			if(_fills != value) {
				removeFillListeners(_fills);
				addFillListeners(_fills = value);
				fillsChanged = true;
			}
		}
		
		
		//*********************************************
		// Constructor
		//*********************************************
		
		public function ComplexFill(fills:Array = null):void {
			shape = new Shape();
			this.fills = fills;
		}
		
		
		//*********************************************
		// Public Methods
		//*********************************************
		
		public function begin(graphics:Graphics, rectangle:Rectangle):void {
			// todo: optimize with more cacheing
			if(rectangle.width > 0 && rectangle.height > 0 && _fills != null && _fills.length > 0) {
				if(_fills.length == 1) { // short cut
					(_fills[0] as IFill).begin(graphics, rectangle);
				} else {
					var matrix:Matrix = new Matrix(1, 0, 0, 1, rectangle.x*-1, rectangle.y*-1);
					if(fillsChanged || bitmapData == null || rectangle.width != bitmapData.width || rectangle.height != bitmapData.height) { // cacheing
						bitmapData = new BitmapData(rectangle.width, rectangle.height, true, 0);
						var g:Graphics = shape.graphics;
						g.clear();
						var lastType:String;
						for each(var fill:IFill in _fills) {
							if(fill is IBlend) {
								if(lastType == "fill") {
									bitmapData.draw(shape, matrix);
								}
								g.clear();
								fill.begin(g, rectangle);
								g.drawRect(rectangle.x, rectangle.y, rectangle.width, rectangle.height);
								fill.end(g);
								bitmapData.draw(shape, matrix, null, (fill as IBlend).blendMode);
								lastType = "blend";
							} else {
								fill.begin(g, rectangle);
								g.drawRect(rectangle.x, rectangle.y, rectangle.width, rectangle.height);
								fill.end(g);
								lastType = "fill";
							}
						}
						
						if(lastType == "fill") {
							bitmapData.draw(shape, matrix);
						}
					}
					matrix.invert();
					graphics.beginBitmapFill(bitmapData, matrix);
				}
			}
		}
		
		public function end(graphics:Graphics):void {
			graphics.endFill();
		}
		
		public function refresh():void {
			fillsChanged = true;
		}
		
		
		//********************************************
		// Private Methods
		//********************************************
		
		private function addFillListeners(fills:Array):void {
			var fill:IFill;
			for each(fill in fills) {
				if(fill is IGraphicsFill) {
					(fill as IGraphicsFill).addEventListener(PropertyChangeEvent.PROPERTY_CHANGE, propertyChangeHandler, false, 0, true);
				}
			}
		}
		
		private function removeFillListeners(fills:Array):void {
			var fill:IFill;
			for each(fill in fills) {
				if(fill is IGraphicsFill) {
					(fill as IGraphicsFill).removeEventListener(PropertyChangeEvent.PROPERTY_CHANGE, propertyChangeHandler, false);
				}
			}
		}
		
		
		//******************************************
		// Event Handlers
		//******************************************
		
		private function propertyChangeHandler(event:PropertyChangeEvent):void{
			refresh();
			dispatchEvent(event);
		}
		
	}
}