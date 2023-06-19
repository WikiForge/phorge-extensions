<?php

class AdminDeleteAnyFileController extends PhabricatorController {

	public function handleRequest( AphrontRequest $request ) {
		$viewer = $this->getViewer();

		if ( !$viewer->getIsAdmin() ) {
			return new Aphront403Response();
		}

		$id = $request->getInt( 'id' );
		$path = $request->getStr( 'path' );

		$file = null;
		if ( $id ) {
			$file = id( new PhabricatorFile() )
				->loadOneWhere( 'id = %d', $id );
		} elseif ( $path ) {
			$file = id( new PhabricatorFile() )
				->loadOneWhere( 'name = %s', $path );
		}

		$title = pht( 'Delete File: %s', $file ? $file->getName() : '' );

		$header = id( new PHUIHeaderView() )
			->setHeader( $title );

		$form = id( new AphrontFormView() )
			->setUser( $viewer )
			->setMethod( 'POST' )
			->setAction( $this->getApplicationURI( 'file/delete/save' ) )
			->appendChild(
				id( new AphrontFormTextControl() )
					->setLabel( pht( 'ID' ) )
					->setName( 'id' )
					->setValue( $file ? $file->getID() : '' )
			)
			->appendChild(
				id( new AphrontFormSubmitControl() )
					->setValue( pht( 'Delete File' ) )
			);

		$view = id( new PHUITwoColumnView() )
			->setHeader( $header )
			->setFooter( $form );

		return $this->newPage()
			->setTitle( $title )
			->appendChild( $view );
	}
}
