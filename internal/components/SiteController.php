<?php
class SiteController extends Controller {
	public function beforeAction() {
		WireFrame::app()->session()->open();

		Localization::connect(WireFrame::app()->db());
		Localization::setLocale(WireFrame::app()->session()->get('locale', 'en-AU'));
		
		return true;
	}
	
	public function actionIndex() {
		if ($this->request->get('locale')) {
			WireFrame::app()->session()->set('locale', $this->request->get('locale'));
			Localization::setLocale($this->request->get('locale'));
		}
		
		$this->render('index');
	}
}
?>