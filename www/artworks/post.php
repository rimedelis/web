<? /** @noinspection PhpIncludeInspection */

use Exceptions\InvalidRequestException;

use function Safe\ini_get;
use function Safe\substr;

try{
	session_start();

	if(HttpInput::RequestMethod() != HTTP_POST){
		throw new InvalidRequestException('Only HTTP POST accepted.');
	}

	if(HttpInput::IsRequestTooLarge()){
		throw new Exceptions\InvalidRequestException('File upload too large.');
	}

	$artwork = new Artwork();

	$artwork->Artist = new Artist();
	$artwork->Artist->Name = HttpInput::Str(POST, 'artist-name', false);
	$artwork->Artist->DeathYear = HttpInput::Int(POST, 'artist-year-of-death');

	$artwork->Name = HttpInput::Str(POST, 'artwork-name', false);
	$artwork->CompletedYear = HttpInput::Int(POST, 'artwork-year');
	$artwork->CompletedYearIsCirca = HttpInput::Bool(POST, 'artwork-year-is-circa', false);
	$artwork->ArtworkTags = Artwork::ParseArtworkTags(HttpInput::Str(POST, 'artwork-tags', false));
	$artwork->Status = COVER_ARTWORK_STATUS_UNVERIFIED;
	$artwork->PublicationYear = HttpInput::Int(POST, 'pd-proof-publication-year');
	$artwork->PublicationYearPageUrl = HttpInput::Str(POST, 'pd-proof-publication-year-page-url', false);
	$artwork->CopyrightPageUrl = HttpInput::Str(POST, 'pd-proof-copyright-page-url', false);
	$artwork->ArtworkPageUrl = HttpInput::Str(POST, 'pd-proof-artwork-page-url', false);
	$artwork->MuseumUrl = HttpInput::Str(POST, 'pd-proof-museum-url', false);
	$artwork->MimeType = ImageMimeType::FromFile($_FILES['color-upload']['tmp_name']);

	// $expectCaptcha = HttpInput::Str(SESSION, 'captcha', false);
	// $actualCaptcha = HttpInput::Str(POST, 'captcha', false);

	// if($expectCaptcha === null || $actualCaptcha === null || mb_strtolower($expectCaptcha) !== mb_strtolower($actualCaptcha)){
	// 	throw new Exceptions\InvalidCaptchaException();
	// }

	$artwork->Create($_FILES['color-upload']);

	$_SESSION['artwork'] = $artwork;
	$_SESSION['artwork-created'] = true;

	http_response_code(303);
	header('Location: /artworks/new');
}
catch(Exceptions\InvalidRequestException){
	http_response_code(405);
}
catch(Exceptions\AppException $exception){
	$_SESSION['artwork'] = $artwork;
	$_SESSION['exception'] = $exception;

	http_response_code(303);
	header('Location: /artworks/new');
}
