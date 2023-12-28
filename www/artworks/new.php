<?php /** @noinspection PhpUndefinedMethodInspection,PhpIncludeInspection */
use function Safe\gmdate;
use function Safe\session_unset;

session_start();

$created = HttpInput::Bool(SESSION, 'artwork-created', false);
$exception = $_SESSION['exception'] ?? null;

/** @var Artwork $artwork */
$artwork = $_SESSION['artwork'] ?? new Artwork();

try{
	if($GLOBALS['User'] === null){
		throw new Exceptions\LoginRequiredException();
	}

	if(!$GLOBALS['User']->Benefits->CanUploadArtwork){
		throw new Exceptions\InvalidPermissionsException();
	}

	// We got here because an artwork was successfully submitted
	if($created){
		http_response_code(201);
		$artwork = new Artwork();
		$artwork->Artist = new Artist();
		session_unset();
	}

	// We got here because an artwork submission had errors and the user has to try again
	if($exception){
		http_response_code(422);
		session_unset();
	}

	if($artwork->Artist === null){
		$artwork->Artist = new Artist();
	}
}
catch(Exceptions\LoginRequiredException){
	Template::RedirectToLogin();
}
catch(Exceptions\InvalidPermissionsException){
	Template::Emit403(); // No permissions to submit artwork
}

?>
<?= Template::Header(
	[
		'title' => 'Submit an Artwork',
		'artwork' => true,
		'highlight' => '',
		'description' => 'Submit public domain artwork to the database for use as cover art.'
	]
) ?>
<main>
	<section class="narrow">
		<h1>Submit an Artwork</h1>

		<?= Template::Error(['exception' => $exception]) ?>

		<? if($created){ ?>
			<p class="message success">Artwork submitted for review!</p>
		<? } ?>

		<form method="post" action="/artworks" enctype="multipart/form-data">
			<fieldset>
				<legend>Artist details</legend>
				<p>If selecting an existing artist, leave the year of death blank.</p>
				<div>
					<label>
						Artist name
						<datalist id="artist-names">
							<? foreach(Library::GetAllArtists() as $existingArtist){ ?>
								<option value="<?= Formatter::ToPlainText($existingArtist->Name) ?>"><?= Formatter::ToPlainText($existingArtist->Name) ?>, d. <? if($existingArtist->DeathYear !== null){ ?><?= $existingArtist->DeathYear ?><? }else{ ?>(unknown)<? } ?></option>
							<? } ?>
						</datalist>
						<input
							type="text"
							name="artist-name"
							list="artist-names"
							required="required"
							value="<?= Formatter::ToPlainText($artwork->Artist->Name) ?>"
						/>
					</label>
					<label>
						Year of death
						<input
							type="number"
							name="artist-year-of-death"
							min="1"
							max="<?= gmdate('Y') ?>"
							value="<?= $artwork->Artist->DeathYear ?>"
						/>
					</label>
				</div>
			</fieldset>
			<fieldset>
				<legend>Artwork details</legend>
				<div>
					<label>
						Artwork name
						<input type="text" name="artwork-name" required="required"
						       value="<?= Formatter::ToPlainText($artwork->Name) ?>"/>
					</label>
					<label for="artwork-year">
						Year of completion
						<label>
							(circa?
							<input
								type="checkbox"
								name="artwork-year-is-circa"
								<? if($artwork->CompletedYearIsCirca){ ?>checked="checked"<? } ?>
							/>)
						</label>
						<input
							type="number"
							id="artwork-year"
							name="artwork-year"
							min="1"
							max="<?= gmdate('Y') ?>"
							value="<?= $artwork->CompletedYear ?>"
						/>
					</label>
				</div>
				<label>
					Tags
					<input
						type="text"
						name="artwork-tags"
						placeholder="A list of comma-separated tags"
						required="required"
						value="<?= Formatter::ToPlainText($artwork->GetArtworkTagsImploded()) ?>"
					/>
				</label>
			</fieldset>
			<fieldset id="pd-proof">
				<legend>Proof of U.S. public domain status</legend>
				<p>See the <a href="/manual/latest/10-art-and-images#10.3.3.7">US-PD clearance section of the <abbr class="acronym">SEMoS</abbr></a> for details.</p>
				<p>PD proof must take the form of:</p>
				<fieldset>
					<label>
						Link to an <a href="/manual/latest/10-art-and-images#10.3.3.7.4">approved museum page</a>
						<input
							type="url"
							name="pd-proof-museum-url"
							value="<?= Formatter::ToPlainText($artwork->MuseumUrl) ?>"
						/>
					</label>
				</fieldset>
				<p>or direct links to page scans for <strong>all</strong> of the following:</p>
				<fieldset>
					<div>
						<label>
							Link to page with year of publication
							<input
								type="url"
								name="pd-proof-publication-year-page-url"
								value="<?= Formatter::ToPlainText($artwork->PublicationYearPageUrl) ?>"
							/>
						</label>
						<label>
							Year of publication
							<input
								type="number"
								name="pd-proof-publication-year"
								min="1"
								max="<?= gmdate('Y') ?>"
								value="<?= $artwork->PublicationYear ?>"
							/>
						</label>
					</div>
					<label>
						Link to page with copyright details (might be same link as above)
						<input
							type="url"
							name="pd-proof-copyright-page-url"
							value="<?= Formatter::ToPlainText($artwork->CopyrightPageUrl) ?>"
						/>
					</label>
					<label>
						Link to page with artwork
						<input
							type="url"
							name="pd-proof-artwork-page-url"
							value="<?= Formatter::ToPlainText($artwork->ArtworkPageUrl) ?>"
						/>
					</label>
				</fieldset>
			</fieldset>
			<!--
			<fieldset>
				<legend></legend>
				<div>
					<label class="captcha" for="captcha">
						Type the letters in the <abbr class="acronym">CAPTCHA</abbr> image
						<input type="text" name="captcha" id="captcha" required="required" autocomplete="off"/>
					</label>
					<img
						src="/images/captcha"
						alt="A visual CAPTCHA."
						height="<?= CAPTCHA_IMAGE_HEIGHT ?>"
						width="<?= CAPTCHA_IMAGE_WIDTH ?>"
					/>
				</div>
			</fieldset>
			-->
			<fieldset>
				<legend>Image file</legend>
				<div>
					<label for="input-color-upload" class="file-upload">
						Attach file
						<br/>
						<input
							type="file"
							name="color-upload"
							id="input-color-upload"
							required="required"
							accept="<?= implode(",", ImageMimeType::Values()) ?>"
						/>
					</label>
				</div>
			</fieldset>
			<div class="footer">
				<button>Submit</button>
			</div>
		</form>
	</section>
</main>
<?= Template::Footer() ?>
