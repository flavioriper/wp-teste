
<a name="readme-top"></a>

<p align="center">
    <a target="_blank" href="https://lightsoncomunicacao.com.br/pt/home/">
        <img src="https://img.shields.io/badge/Powered%20by-Lightson-orange.svg?style=for-the-badge&logo=wordpress">
    </a>
    &nbsp; &nbsp; &nbsp; &nbsp;
    <img src="https://img.shields.io/badge/Status-Em%20desenvolvimento-blue.svg?style=for-the-badge">
</p>



<!-- PROJECT LOGO -->
<br />
<div align="center">
  <a href="https://yuppins.com">
    <img src="docs/img/logo.png" alt="Logo" width="300">
  </a>


<!-- GETTING STARTED -->
## Primeiros passos

Este repositório serve para configurar o módulo de wordpress que o projeto utiliza.
Para colocar uma cópia local em execução, siga estas etapas de exemplo simples.

### Pré-requisitos

O projeto possuí dependencias e requisitos para rodar em sua máquina local, sendo necessário possuir PHP instalado ou servidor web local, recomendamos o <a href="https://laragon.org/download/index.html" target="_blank">Laragon</a> caso utilize windows.



### Instalação


1. Clone o repositório:
    ```sh
   git clone https://github.com/yuppins/wp-module.git
   ```
2. Mude o arquivo ```wp-config-sample.php``` para ```wp-config.php``` e edite o arquivo para adicionar as informações de banco.
3. Atualize seu banco com o dump mais recente (solicite ao dev responsável pelo projeto caso não tenha).
4. Atualize no banco as variáveis de ambiente do wordpress na tabela ```wp_options``` (geralmente ```siteurl``` e ```home```).
5. Caso seja necessário, atualize também o path do projeto em ```wp-content/webp-express/config/```, arquivos: ```wod-options.json``` e ```config.json```
6. O webp-express utiliza ```extension=gd``` para as imagens, é importante manter liberado no ```php.ini``` para melhor compatibilidade.


<p align="right">(<a href="#readme-top">back to top</a>)</p>